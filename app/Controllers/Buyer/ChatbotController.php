<?php

namespace App\Controllers\Buyer;

use App\Controllers\BaseController;
use App\Models\LandListings;
use Config\ChatbotTraining;
use Config\Database;

class ChatbotController extends BaseController
{
    private $geminiApiKey;
    private $geoapifyApiKey;
    private $geminiApiBase = 'https://generativelanguage.googleapis.com/v1beta/models/';
    private $geminiModels = ['gemini-2.5-flash', 'gemini-2.5-pro', 'gemini-2.0-flash', 'gemini-1.5-flash-latest', 'gemini-1.5-pro-latest'];
    private ?array $geminiLastError = null;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->geminiApiKey = $this->resolveGeminiApiKey();
        $this->geoapifyApiKey = $this->resolveGeoapifyApiKey();
    }

    private function resolveGeminiApiKey(): string
    {
        return (string) (
            env('GEMINI_API_KEY')
            ?? $_ENV['GEMINI_API_KEY']
            ?? $_SERVER['GEMINI_API_KEY']
            ?? getenv('GEMINI_API_KEY')
            ?? ''
        );
    }

    private function resolveGeoapifyApiKey(): string
    {
        return (string) (
            env('GEOAPIFY_API_KEY')
            ?? $_ENV['GEOAPIFY_API_KEY']
            ?? $_SERVER['GEOAPIFY_API_KEY']
            ?? getenv('GEOAPIFY_API_KEY')
            ?? ''
        );
    }

    public function sendMessage()
    {
        if (!$this->request->isAJAX() && !$this->request->isJSON()) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Invalid request method.']);
        }

        $userMessage = trim((string) $this->request->getPost('message'));
        if ($userMessage === '') {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Message cannot be empty.']);
        }

        try {
            $this->geminiLastError = null;
            $trainingMatch = $this->matchTrainingIntent($userMessage);

            if ($this->isGreetingMessage($userMessage)) {
                $greetingResponse = $this->buildGreetingResponse($userMessage);
                return $this->response->setStatusCode(200)->setJSON([
                    'status' => 'success',
                    'message' => $greetingResponse,
                    'listings' => [],
                ]);
            }

            if (($trainingMatch['reply_type'] ?? '') === 'availability') {
                $intent = $this->extractUserIntent($userMessage);
                $listingObjects = $this->getNasugbuListingObjects($userMessage);
                log_message('notice', 'Availability query: Found ' . count($listingObjects) . ' listings');

                if ($listingObjects === []) {
                    return $this->response->setStatusCode(200)->setJSON([
                        'status' => 'success',
                        'message' => 'Sorry 😔 I couldn’t find any available Nasugbu listings right now. Please try another filter like barangay, budget, or nearby landmark.',
                        'listings' => [],
                    ]);
                }

                $recommendation = $this->recommendListingsByIntent($listingObjects, $intent);
                $topListings = array_slice($recommendation['top'] ?? $listingObjects, 0, 3);

                if ($this->geminiApiKey !== '') {
                    log_message('notice', 'Calling Gemini API for availability response');
                    $systemPrompt = $this->buildNasugbuRecommendationPrompt($listingObjects, $topListings, $intent);
                    $aiResponse = $this->callGeminiApi($userMessage, $systemPrompt);
                    if ($aiResponse !== null && $aiResponse !== '') {
                        log_message('notice', 'Gemini returned response');
                    } else {
                        log_message('warning', 'Gemini returned null/empty response');
                    }
                } else {
                    log_message('warning', 'Gemini API key not configured');
                    $aiResponse = null;
                }

                if ($aiResponse === null || $aiResponse === '') {
                    $aiResponse = $this->buildAvailabilityReply($topListings);
                }

                return $this->response->setStatusCode(200)->setJSON([
                    'status' => 'success',
                    'message' => $aiResponse,
                    'listings' => $topListings,
                ]);
            }

            if (!$this->isPropertyRelatedQuery($userMessage)) {
                return $this->response->setStatusCode(200)->setJSON([
                    'status' => 'success',
                    'message' => 'Sorry, I can only assist with land listings and property-related inquiries on Landly.',
                    'listings' => [],
                ]);
            }

            $intent = $this->extractUserIntent($userMessage);
            if ($this->isOutsideNasugbuRequest($userMessage, $intent)) {
                return $this->response->setStatusCode(200)->setJSON([
                    'status' => 'success',
                    'message' => 'Hi 😊 Currently, I can only assist with properties located in Nasugbu, Batangas.',
                    'listings' => [],
                ]);
            }

            $includeSurroundings = $this->shouldIncludeSurroundings($userMessage)
                || !empty($intent['near_landmark'])
                || !empty($intent['surroundings']);

            $allListings = $this->getAvailableNasugbuListings(80);
            log_message('notice', 'Property query: Fetched ' . count($allListings) . ' listings from database');
            $allListingObjects = array_map(
                fn(array $listing): array => $this->buildListingDataObject($listing, $includeSurroundings),
                $allListings
            );

            if ($allListingObjects === []) {
                return $this->response->setStatusCode(200)->setJSON([
                    'status' => 'success',
                    'message' => 'Sorry 😔 I couldn’t find any listings that match your request right now. You may try adjusting your preferences or exploring nearby areas in Nasugbu.',
                    'listings' => [],
                ]);
            }

            $guidedQuestion = $this->getGuidedQuestion($userMessage, $intent);
            if ($guidedQuestion !== null) {
                return $this->response->setStatusCode(200)->setJSON([
                    'status' => 'success',
                    'message' => $guidedQuestion,
                    'listings' => array_slice($allListingObjects, 0, 3),
                ]);
            }

            $recommendation = $this->recommendListingsByIntent($allListingObjects, $intent);
            $topListings = array_slice($recommendation['top'] ?? [], 0, 3);

            if ($recommendation['barangay_empty'] ?? false) {
                $aiResponse = 'Sorry 😔 there are no listings available in that area right now. However, here are nearby options you may consider.';
            } elseif ($topListings === []) {
                $aiResponse = 'Sorry 😔 I couldn’t find any listings that match your request right now. You may try adjusting your preferences or exploring nearby areas in Nasugbu.';
            } elseif ($this->geminiApiKey !== '') {
                log_message('notice', 'Calling Gemini API for property recommendation');
                $systemPrompt = $this->buildNasugbuRecommendationPrompt($allListingObjects, $topListings, $intent);
                $aiResponse = $this->callGeminiApi($userMessage, $systemPrompt);
                if ($aiResponse !== null && $aiResponse !== '') {
                    log_message('notice', 'Gemini returned response for property query');
                } else {
                    log_message('warning', 'Gemini returned null/empty for property query');
                }
            } else {
                log_message('warning', 'Gemini API key not configured for property query');
                $aiResponse = null;
            }

            if ($aiResponse === null || $aiResponse === '') {
                $aiResponse = $this->buildRuleBasedRecommendationReply(
                    $topListings,
                    (bool) ($recommendation['exact'] ?? false),
                    (bool) ($recommendation['barangay_empty'] ?? false)
                );
            }

            return $this->response->setStatusCode(200)->setJSON([
                'status' => 'success',
                'message' => $aiResponse,
                'listings' => $topListings,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Chatbot error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'An error occurred while processing your message.']);
        }
    }

    private function searchListingsForContext(string $message): array
    {
        try {
            $keywords = $this->extractKeywords($message);
            if ($keywords === []) {
                return $this->getRandomListings(5, true);
            }

            $landListingsModel = new LandListings();
            $builder = $landListingsModel->builder();
            $builder->select('listing_id, title, description, barangay, city, province, price, developing_area, listing_status, is_verified_listing, created_at');

            $db = Database::connect();
            if ($db->tableExists('listing_locations')) {
                $builder->select('listing_locations.latitude AS listing_latitude, listing_locations.longitude AS listing_longitude');
                $builder->join('listing_locations', 'listing_locations.listing_id = land_listings.listing_id', 'left');
            }

            $builder->groupStart();
            $builder->where('listing_status', 'available');
            $builder->orWhere('listing_status', 'Available');
            $builder->groupEnd();

            $builder->groupStart();
            $builder->where('is_verified_listing', 'true');
            $builder->orWhere('is_verified_listing', '1');
            $builder->groupEnd();
            $builder->groupStart();

            foreach ($keywords as $keyword) {
                $builder->orLike('title', $keyword);
                $builder->orLike('description', $keyword);
                $builder->orLike('barangay', $keyword);
                $builder->orLike('city', $keyword);
                $builder->orLike('province', $keyword);
            }

            $builder->groupEnd();

            $listings = $builder->limit(5)->get()->getResultArray();
            foreach ($listings as &$listing) {
                $listing['primary_image_url'] = $this->getPrimaryImageUrl((int) ($listing['listing_id'] ?? 0));
            }

            if ($listings !== []) {
                return $listings;
            }

            return $this->getRandomListings(5, true);
        } catch (\Exception $e) {
            log_message('error', 'Search listings error: ' . $e->getMessage());
            return [];
        }
    }

    private function isPropertyRelatedQuery(string $message): bool
    {
        return preg_match('/\b(land|lot|listing|property|properties|real estate|beach|school|church|hospital|barangay|brgy|nasugbu|price|budget|sqm|hectare|farm|residential|commercial|agricultural)\b/i', $message) === 1;
    }

    private function matchTrainingIntent(string $message): array
    {
        $normalizedMessage = $this->normalizeTrainingText($message);
        $training = config('ChatbotTraining');
        $intents = $training instanceof ChatbotTraining ? $training->intents : [];

        foreach ($intents as $intent) {
            foreach (($intent['examples'] ?? []) as $example) {
                $normalizedExample = $this->normalizeTrainingText((string) $example);
                if ($normalizedExample === '' || $normalizedMessage === '') {
                    continue;
                }

                if ($normalizedMessage === $normalizedExample || str_contains($normalizedMessage, $normalizedExample) || str_contains($normalizedExample, $normalizedMessage)) {
                    return [
                        'name' => (string) ($intent['name'] ?? ''),
                        'reply_type' => (string) ($intent['reply_type'] ?? ''),
                    ];
                }
            }
        }

        return ['name' => '', 'reply_type' => ''];
    }

    private function normalizeTrainingText(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9\s]/i', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;
        return trim($text);
    }

    private function isGreetingMessage(string $message): bool
    {
        return preg_match('/\b(hi|hello|hey|hai|hallo|good morning|good afternoon|good evening|kumusta|kamusta)\b/i', $message) === 1;
    }

    private function buildGreetingResponse(string $userMessage): string
    {
        if ($this->geminiApiKey === '') {
            return 'Hi 😊 What kind of land in Nasugbu are you looking for?';
        }

        $systemPrompt = "You are Landly AI Assistant, a friendly real estate assistant for a land marketplace named Landly.

RULES:
- Respond warmly to greetings.
- Keep the reply short, conversational, and helpful.
- Mention that you can help with land listings in Nasugbu, Batangas.
- Ask one short follow-up question about what the user wants.
- Do not mention unavailable data.
- Do not provide off-topic answers.

STYLE:
- Simple English or Taglish.
- Friendly and concise.
- Can use light emoji sparingly.";

        $response = $this->callGeminiApi($userMessage, $systemPrompt);
        return $response !== null && $response !== ''
            ? $response
            : 'Hi 😊 What kind of land in Nasugbu are you looking for?';
    }

    private function extractUserIntent(string $message): array
    {
        $intent = [
            'barangay' => '',
            'location_phrase' => '',
            'property_type' => '',
            'near_landmark' => '',
            'surroundings' => [],
            'budget' => null,
        ];

        if (preg_match('/\b(?:brgy|barangay)\.?\s*([a-z][a-z\s-]{1,40})/i', $message, $matches)) {
            $intent['barangay'] = trim((string) ($matches[1] ?? ''));
        }

        if (preg_match('/\b(?:in|at|around)\s+([a-z][a-z\s-]{1,60})(?:[?.!,]|$)/i', $message, $matches)) {
            $intent['location_phrase'] = trim((string) ($matches[1] ?? ''));
        }

        if (preg_match('/\bnear(?:by)?\s+(?:to\s+)?(?:a|an|the)?\s*([a-z][a-z\s-]{1,40})(?:\s+in\s+[a-z\s-]+)?(?:[?.!,]|$)/i', $message, $matches)) {
            $intent['near_landmark'] = trim((string) ($matches[1] ?? ''));
        }

        $propertyTypes = ['farm lot', 'residential', 'commercial', 'agricultural', 'industrial'];
        foreach ($propertyTypes as $type) {
            if (stripos($message, $type) !== false) {
                $intent['property_type'] = $type;
                break;
            }
        }

        $surroundings = [];
        foreach (['school', 'beach', 'church', 'hospital', 'barangay', 'brgy'] as $term) {
            if (preg_match('/\b' . preg_quote($term, '/') . '\b/i', $message) === 1) {
                $surroundings[] = $term;
            }
        }
        $intent['surroundings'] = array_values(array_unique($surroundings));
        $intent['budget'] = $this->extractBudgetFromMessage($message);

        return $intent;
    }

    private function extractBudgetFromMessage(string $message): ?array
    {
        $message = str_replace(['₱', 'PHP', 'Php', 'php', 'pesos', 'peso'], ' ', $message);

        if (preg_match('/\bbetween\s+([\d,.]+\s*[kKmM]?)\s+(?:and|to)\s+([\d,.]+\s*[kKmM]?)/i', $message, $matches)) {
            $min = $this->parseMoneyToken((string) ($matches[1] ?? ''));
            $max = $this->parseMoneyToken((string) ($matches[2] ?? ''));
            if ($min !== null && $max !== null) {
                return ['min' => min($min, $max), 'max' => max($min, $max), 'target' => (int) round(($min + $max) / 2)];
            }
        }

        if (preg_match('/\b(?:under|below|less than|max(?:imum)?|up to|not more than)\s+([\d,.]+\s*[kKmM]?)/i', $message, $matches)) {
            $max = $this->parseMoneyToken((string) ($matches[1] ?? ''));
            return $max !== null ? ['min' => null, 'max' => $max, 'target' => $max] : null;
        }

        if (preg_match('/\b(?:over|above|at least|min(?:imum)?|from)\s+([\d,.]+\s*[kKmM]?)/i', $message, $matches)) {
            $min = $this->parseMoneyToken((string) ($matches[1] ?? ''));
            return $min !== null ? ['min' => $min, 'max' => null, 'target' => $min] : null;
        }

        if (preg_match('/\b([\d,.]+\s*[kKmM]?)\b/', $message, $matches)) {
            $target = $this->parseMoneyToken((string) ($matches[1] ?? ''));
            return $target !== null ? ['min' => null, 'max' => null, 'target' => $target] : null;
        }

        if (preg_match('/\b([\d,.]+)\s*(thousand|k|m|million)\b/i', $message, $matches)) {
            $suffix = strtolower((string) ($matches[2] ?? ''));
            $token = (string) ($matches[1] ?? '');
            if ($suffix === 'thousand') {
                $token .= 'k';
            } elseif ($suffix === 'million') {
                $token .= 'm';
            } else {
                $token .= $suffix;
            }

            $target = $this->parseMoneyToken($token);
            return $target !== null ? ['min' => null, 'max' => null, 'target' => $target] : null;
        }

        return null;
    }

    private function parseMoneyToken(string $raw): ?int
    {
        $normalized = strtolower(str_replace([',', ' ', '₱'], '', trim($raw)));
        if ($normalized === '' || !preg_match('/^\d+(?:\.\d+)?[km]?$/', $normalized)) {
            return null;
        }

        $multiplier = 1;
        if (str_ends_with($normalized, 'k')) {
            $multiplier = 1000;
            $normalized = substr($normalized, 0, -1);
        } elseif (str_ends_with($normalized, 'm')) {
            $multiplier = 1000000;
            $normalized = substr($normalized, 0, -1);
        }

        if (!is_numeric($normalized)) {
            return null;
        }

        return (int) round(((float) $normalized) * $multiplier);
    }

    private function isOutsideNasugbuRequest(string $message, array $intent): bool
    {
        $messageLower = strtolower($message);
        if (str_contains($messageLower, 'outside nasugbu')) {
            return true;
        }

        $location = strtolower(trim((string) ($intent['location_phrase'] ?? '')));
        if ($location === '') {
            return false;
        }

        if (str_contains($location, 'nasugbu')) {
            return false;
        }

        if (str_contains($location, 'barangay') || str_contains($location, 'brgy')) {
            return false;
        }

        return true;
    }

    private function getAvailableNasugbuListings(int $limit = 80): array
    {
        try {
            $landListingsModel = new LandListings();
            $builder = $landListingsModel->builder();
            $builder->select('land_listings.listing_id, land_listings.title, land_listings.description, land_listings.barangay, land_listings.city, land_listings.province, land_listings.price, land_listings.developing_area, land_listings.listing_status, land_listings.is_verified_listing, land_listings.created_at');

            $db = Database::connect();
            if ($db->tableExists('listing_locations')) {
                $builder->select('listing_locations.latitude AS listing_latitude, listing_locations.longitude AS listing_longitude');
                $builder->join('listing_locations', 'listing_locations.listing_id = land_listings.listing_id', 'left');
            }

            $builder->groupStart();
            $builder->where('listing_status', 'available');
            $builder->orWhere('listing_status', 'Available');
            $builder->orWhere('listing_status', '');
            $builder->groupEnd();

            $builder->groupStart();
            $builder->where('is_verified_listing', 'true');
            $builder->orWhere('is_verified_listing', '1');
            $builder->orWhere('is_verified_listing', 'pending');
            $builder->orWhere('is_verified_listing', '');
            $builder->groupEnd();

            $builder->groupStart();
            $builder->like('city', 'Nasugbu');
            $builder->orLike('barangay', 'Nasugbu');
            $builder->orLike('province', 'Batangas');
            $builder->orLike('title', 'Nasugbu');
            $builder->orLike('description', 'Nasugbu');
            $builder->groupEnd();

            log_message('debug', 'Executing Nasugbu-filtered query');
            $rows = $builder->limit($limit)->get()->getResultArray();
            log_message('debug', 'Nasugbu query returned ' . count($rows) . ' rows');
            
            if (count($rows) === 0) {
                log_message('notice', 'No Nasugbu-filtered listings found. Fetching all available listings.');
                $builder = $landListingsModel->builder();
                $builder->select('land_listings.listing_id, land_listings.title, land_listings.description, land_listings.barangay, land_listings.city, land_listings.province, land_listings.price, land_listings.developing_area, land_listings.listing_status, land_listings.is_verified_listing, land_listings.created_at');
                if ($db->tableExists('listing_locations')) {
                    $builder->select('listing_locations.latitude AS listing_latitude, listing_locations.longitude AS listing_longitude');
                    $builder->join('listing_locations', 'listing_locations.listing_id = land_listings.listing_id', 'left');
                }
                $builder->groupStart();
                $builder->where('listing_status', 'available');
                $builder->orWhere('listing_status', 'Available');
                $builder->orWhere('listing_status', '');
                $builder->groupEnd();
                log_message('debug', 'Executing fallback (all available) query');
                $rows = $builder->limit($limit)->get()->getResultArray();
                log_message('debug', 'Fallback query returned ' . count($rows) . ' rows');
            }

            foreach ($rows as &$row) {
                $row['primary_image_url'] = $this->getPrimaryImageUrl((int) ($row['listing_id'] ?? 0));
            }

            return $rows;
        } catch (\Exception $e) {
            log_message('error', 'Get available Nasugbu listings error: ' . $e->getMessage());
            return [];
        }
    }

    private function getNasugbuListingObjects(string $userMessage): array
    {
        $includeSurroundings = $this->shouldIncludeSurroundings($userMessage);
        $listings = $this->getAvailableNasugbuListings(80);

        return array_map(
            fn(array $listing): array => $this->buildListingDataObject($listing, $includeSurroundings),
            $listings
        );
    }

    private function getGuidedQuestion(string $userMessage, array $intent): ?string
    {
        $isBroad = preg_match('/\b(find|looking|recommend|suggest|help me|need|want)\b/i', $userMessage) === 1;
        if (!$isBroad) {
            return null;
        }

        $hasSpecifics = trim((string) ($intent['property_type'] ?? '')) !== ''
            || trim((string) ($intent['barangay'] ?? '')) !== ''
            || trim((string) ($intent['near_landmark'] ?? '')) !== ''
            || !empty($intent['surroundings'])
            || !empty($intent['budget']);

        if ($hasSpecifics) {
            return null;
        }

        $session = session();
        $state = $session->get('landly_ai_chatbot_step');
        if (!is_string($state) || $state === '') {
            $state = 'property_type';
        }

        $questions = [
            'property_type' => 'Sure 😊 What type of property are you looking for? (farm lot, residential, commercial, etc.)',
            'barangay' => 'Got it 👍 Which barangay in Nasugbu do you prefer?',
            'budget' => 'Nice. What is your budget range for the land?',
            'surroundings' => 'Any preferred surroundings? (near beach, school, church, or barangay center)',
        ];

        $nextState = match ($state) {
            'property_type' => 'barangay',
            'barangay' => 'budget',
            'budget' => 'surroundings',
            default => 'property_type',
        };

        $session->set('landly_ai_chatbot_step', $nextState);
        return $questions[$state] ?? $questions['property_type'];
    }

    private function recommendListingsByIntent(array $listings, array $intent): array
    {
        $barangay = strtolower(trim((string) ($intent['barangay'] ?? '')));
        $candidates = $listings;
        $barangayEmpty = false;

        if ($barangay !== '') {
            $barangayMatches = array_values(array_filter($listings, static function (array $listing) use ($barangay): bool {
                return str_contains(strtolower((string) ($listing['location'] ?? '')), $barangay);
            }));

            if ($barangayMatches === []) {
                $barangayEmpty = true;
            } else {
                $candidates = $barangayMatches;
            }
        }

        $scored = [];
        $exactFound = false;
        foreach ($candidates as $listing) {
            $score = $this->scoreListingByIntent($listing, $intent);
            $listing['match_score'] = $score;
            $scored[] = $listing;

            if ($this->isExactListingMatch($listing, $intent)) {
                $exactFound = true;
            }
        }

        usort($scored, static function (array $a, array $b): int {
            return ((int) ($b['match_score'] ?? 0)) <=> ((int) ($a['match_score'] ?? 0));
        });

        if ($barangayEmpty) {
            $scored = array_values($scored);
        }

        return [
            'top' => array_slice($scored, 0, 3),
            'exact' => $exactFound,
            'barangay_empty' => $barangayEmpty,
        ];
    }

    private function scoreListingByIntent(array $listing, array $intent): int
    {
        $score = 1;
        $text = strtolower(
            trim((string) ($listing['title'] ?? '')) . ' ' .
            trim((string) ($listing['description'] ?? '')) . ' ' .
            trim((string) ($listing['location'] ?? ''))
        );

        $propertyType = strtolower(trim((string) ($intent['property_type'] ?? '')));
        if ($propertyType !== '' && str_contains($text, $propertyType)) {
            $score += 12;
        }

        $barangay = strtolower(trim((string) ($intent['barangay'] ?? '')));
        if ($barangay !== '' && str_contains(strtolower((string) ($listing['location'] ?? '')), $barangay)) {
            $score += 12;
        }

        $landmarkTerms = $this->getLandmarkTerms(strtolower(trim((string) ($intent['near_landmark'] ?? ''))));
        foreach ($intent['surroundings'] ?? [] as $surrounding) {
            $landmarkTerms = array_merge($landmarkTerms, $this->getLandmarkTerms((string) $surrounding));
        }
        $landmarkTerms = array_values(array_unique(array_filter($landmarkTerms)));

        if ($landmarkTerms !== []) {
            foreach ($landmarkTerms as $term) {
                if (str_contains($text, strtolower($term))) {
                    $score += 5;
                }
            }

            $nearbyPlaces = is_array($listing['nearby_places'] ?? null) ? $listing['nearby_places'] : [];
            foreach ($nearbyPlaces as $place) {
                $placeText = strtolower(
                    trim((string) ($place['name'] ?? '')) . ' ' .
                    trim((string) ($place['category'] ?? '')) . ' ' .
                    trim((string) ($place['formatted'] ?? ''))
                );

                foreach ($landmarkTerms as $term) {
                    if ($term !== '' && str_contains($placeText, strtolower($term))) {
                        $score += 8;
                    }
                }
            }
        }

        $budget = $intent['budget'] ?? null;
        $price = (int) ($listing['price'] ?? 0);
        if (is_array($budget)) {
            $min = $budget['min'] ?? null;
            $max = $budget['max'] ?? null;
            $target = $budget['target'] ?? null;

            if ($min !== null && $price >= (int) $min) {
                $score += 8;
            }
            if ($max !== null && $price <= (int) $max) {
                $score += 8;
            }

            if ($target !== null && $target > 0) {
                $distanceRatio = abs($price - (int) $target) / max((int) $target, 1);
                $score += max(0, 10 - (int) floor($distanceRatio * 10));
            }
        }

        return $score;
    }

    private function isExactListingMatch(array $listing, array $intent): bool
    {
        $text = strtolower(
            trim((string) ($listing['title'] ?? '')) . ' ' .
            trim((string) ($listing['description'] ?? '')) . ' ' .
            trim((string) ($listing['location'] ?? ''))
        );

        $propertyType = strtolower(trim((string) ($intent['property_type'] ?? '')));
        if ($propertyType !== '' && !str_contains($text, $propertyType)) {
            return false;
        }

        $barangay = strtolower(trim((string) ($intent['barangay'] ?? '')));
        if ($barangay !== '' && !str_contains(strtolower((string) ($listing['location'] ?? '')), $barangay)) {
            return false;
        }

        $budget = $intent['budget'] ?? null;
        $price = (int) ($listing['price'] ?? 0);
        if (is_array($budget)) {
            if (($budget['min'] ?? null) !== null && $price < (int) $budget['min']) {
                return false;
            }
            if (($budget['max'] ?? null) !== null && $price > (int) $budget['max']) {
                return false;
            }
        }

        return true;
    }

    private function buildNasugbuRecommendationPrompt(array $allListings, array $topListings, array $intent): string
    {
        return "You are Landly AI Assistant, a smart real estate assistant for land listings.

STRICT CONSTRAINTS:
- Process and recommend listings only in Nasugbu, Batangas.
- If user asks outside Nasugbu, answer: Hi 😊 Currently, I can only assist with properties located in Nasugbu, Batangas.
- If user asks unrelated topics, answer exactly: Sorry, I can only assist with land listings and property-related inquiries on Landly.
- Use only the listing data provided below. Do not invent listings.

RECOMMENDATION RULES:
- Prioritize available listings, budget fit, and preference match (near school/beach/church/barangay, farm lot/residential, etc.).
- If there is no exact match, say: I couldn't find an exact match, but here are the closest available options.
- Keep response friendly, concise, simple English or Taglish.
- Start with a short explanation then provide top 3 recommendations.

INTENT:\n" . json_encode($intent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
            . "\n\nTOP_RECOMMENDATIONS:\n" . json_encode($topListings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
            . "\n\nALL_NASUGBU_LISTINGS_JSON:\n" . json_encode($allListings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    private function buildRuleBasedRecommendationReply(array $topListings, bool $exactMatch, bool $barangayEmpty): string
    {
        if ($topListings === []) {
            return 'Sorry 😔 I couldn’t find any listings that match your request right now. You may try adjusting your preferences or exploring nearby areas in Nasugbu.';
        }

        if ($barangayEmpty) {
            $prefix = 'Sorry 😔 there are no listings available in that area right now. However, here are nearby options you may consider.';
        } elseif (!$exactMatch) {
            $prefix = 'I couldn\'t find an exact match, but here are the closest available options.';
        } else {
            $prefix = 'Here are the best available land options in Nasugbu based on your request.';
        }

        $lines = [];
        foreach (array_slice($topListings, 0, 3) as $listing) {
            $title = trim((string) ($listing['title'] ?? 'Property'));
            $location = trim((string) ($listing['location'] ?? 'Location unavailable'));
            $price = number_format((int) ($listing['price'] ?? 0));
            $size = (float) ($listing['size_sqm'] ?? 0);
            $lines[] = $title . ' - ' . $location . ' - ₱' . $price . ' - ' . rtrim(rtrim(number_format($size, 2), '0'), '.') . ' sqm';
        }

        return $prefix . "\n" . implode("\n", $lines);
    }

    private function searchNearbyListingsForRecommendation(array $intent): array
    {
        try {
            $landListingsModel = new LandListings();
            $builder = $landListingsModel->builder();
            $builder->select('listing_id, title, description, barangay, city, province, price, developing_area, listing_status, is_verified_listing, created_at');

            $db = Database::connect();
            if ($db->tableExists('listing_locations')) {
                $builder->select('listing_locations.latitude AS listing_latitude, listing_locations.longitude AS listing_longitude');
                $builder->join('listing_locations', 'listing_locations.listing_id = land_listings.listing_id', 'left');
            }

            $builder->groupStart();
            $builder->where('listing_status', 'available');
            $builder->orWhere('listing_status', 'Available');
            $builder->groupEnd();

            $builder->groupStart();
            $builder->where('is_verified_listing', 'true');
            $builder->orWhere('is_verified_listing', '1');
            $builder->groupEnd();

            $location = trim((string) ($intent['location'] ?? ''));
            if ($location !== '') {
                $builder->groupStart();
                $builder->like('barangay', $location);
                $builder->orLike('city', $location);
                $builder->orLike('province', $location);
                $builder->groupEnd();
            }

            $candidateListings = $builder->limit(25)->get()->getResultArray();
            if ($candidateListings === []) {
                return $this->getRandomListings(5, true);
            }

            $ranked = [];
            foreach ($candidateListings as $listing) {
                $listing['primary_image_url'] = $this->getPrimaryImageUrl((int) ($listing['listing_id'] ?? 0));
                $score = $this->scoreListingForNearbyIntent($listing, $intent);
                $listing['match_score'] = $score;
                $ranked[] = $listing;
            }

            usort($ranked, static function (array $a, array $b): int {
                return ((int) ($b['match_score'] ?? 0)) <=> ((int) ($a['match_score'] ?? 0));
            });

            $bestMatches = array_values(array_filter($ranked, static function (array $listing): bool {
                return ((int) ($listing['match_score'] ?? 0)) > 0;
            }));

            if ($bestMatches !== []) {
                return array_slice($bestMatches, 0, 10);
            }

            return array_slice($ranked, 0, 10);
        } catch (\Exception $e) {
            log_message('error', 'Nearby recommendation search error: ' . $e->getMessage());
            return $this->searchListingsForContext(($intent['landmark'] ?? '') . ' ' . ($intent['location'] ?? ''));
        }
    }

    private function scoreListingForNearbyIntent(array $listing, array $intent): int
    {
        $score = 0;
        $locationNeedle = strtolower(trim((string) ($intent['location'] ?? '')));
        $landmarkNeedle = strtolower(trim((string) ($intent['landmark'] ?? '')));

        if ($locationNeedle !== '') {
            $locationText = strtolower($this->formatLocation($listing));
            if ($locationText !== '' && str_contains($locationText, $locationNeedle)) {
                $score += 5;
            }
        }

        $landmarkTerms = $this->getLandmarkTerms($landmarkNeedle);
        $contextText = strtolower(
            trim((string) ($listing['title'] ?? '')) . ' ' .
            trim((string) ($listing['description'] ?? '')) . ' ' .
            $this->formatLocation($listing)
        );

        foreach ($landmarkTerms as $term) {
            if ($term !== '' && str_contains($contextText, strtolower($term))) {
                $score += 4;
            }
        }

        $coordinates = $this->extractListingCoordinates($listing);
        if ($coordinates !== null) {
            $places = $this->fetchNearbyPlacesSummary($coordinates['lat'], $coordinates['lng']);

            foreach ($places as $place) {
                $haystack = strtolower(
                    trim((string) ($place['name'] ?? '')) . ' ' .
                    trim((string) ($place['category'] ?? '')) . ' ' .
                    trim((string) ($place['formatted'] ?? ''))
                );

                foreach ($landmarkTerms as $term) {
                    if ($term !== '' && str_contains($haystack, strtolower($term))) {
                        $score += 10;
                    }
                }
            }
        }

        return $score;
    }

    private function extractNearbyIntent(string $message): ?array
    {
        $normalized = strtolower(trim($message));
        if ($normalized === '' || preg_match('/\bnear\b/i', $normalized) !== 1) {
            return null;
        }

        $landmark = '';
        $location = '';

        if (preg_match('/\bnear(?:by)?\s+(?:a|an|the)?\s*([a-z\s-]+?)(?:\s+in\s+([a-z\s-]+))?(?:[?.!,]|$)/i', $message, $matches)) {
            $landmark = trim((string) ($matches[1] ?? ''));
            $location = trim((string) ($matches[2] ?? ''));
        }

        if ($location === '' && preg_match('/\b(?:in|at)\s+([a-z\s-]+)(?:[?.!,]|$)/i', $message, $matches)) {
            $location = trim((string) ($matches[1] ?? ''));
        }

        if ($landmark === '') {
            return null;
        }

        return [
            'landmark' => $landmark,
            'location' => $location,
        ];
    }

    private function getLandmarkTerms(string $landmark): array
    {
        $landmark = strtolower(trim($landmark));
        if ($landmark === '') {
            return [];
        }

        $terms = [$landmark];

        if (str_contains($landmark, 'slaughter')) {
            $terms[] = 'slaughterhouse';
            $terms[] = 'slaughter house';
            $terms[] = 'abattoir';
            $terms[] = 'meat processing';
        }

        if (
            str_contains($landmark, 'school')
            || str_contains($landmark, 'elementary')
            || str_contains($landmark, 'elementart')
            || str_contains($landmark, 'elementay')
            || str_contains($landmark, 'elementar')
        ) {
            $terms[] = 'school';
            $terms[] = 'elementary';
            $terms[] = 'primary school';
            $terms[] = 'education.school';
        }

        if (str_contains($landmark, 'hospital')) {
            $terms[] = 'hospital';
            $terms[] = 'clinic';
            $terms[] = 'healthcare.hospital';
        }

        if (str_contains($landmark, 'church')) {
            $terms[] = 'church';
            $terms[] = 'chapel';
            $terms[] = 'place_of_worship';
        }

        if (str_contains($landmark, 'beach')) {
            $terms[] = 'beach';
            $terms[] = 'beach_resort';
            $terms[] = 'coast';
        }

        if (str_contains($landmark, 'brgy') || str_contains($landmark, 'barangay')) {
            $terms[] = 'brgy';
            $terms[] = 'barangay';
            $terms[] = 'barangay hall';
            $terms[] = 'village';
        }

        return array_values(array_unique(array_filter($terms)));
    }

    private function buildNearbyRecommendationReply(array $intent, array $listingDataObjects): string
    {
        if ($listingDataObjects === []) {
            return 'I could not find available listings for that nearby request. Try another landmark or location (for example: near a school in Nasugbu).';
        }

        $landmark = trim((string) ($intent['landmark'] ?? 'that place'));
        $location = trim((string) ($intent['location'] ?? ''));
        $lines = [];

        foreach (array_slice($listingDataObjects, 0, 5) as $listing) {
            $title = trim((string) ($listing['title'] ?? 'Property'));
            $listingLocation = trim((string) ($listing['location'] ?? 'Location unavailable'));
            $price = isset($listing['price']) ? number_format((int) $listing['price']) : 'N/A';

            $nearbyHint = '';
            $nearbyPlaces = is_array($listing['nearby_places'] ?? null) ? $listing['nearby_places'] : [];
            if ($nearbyPlaces !== []) {
                $firstPlace = $nearbyPlaces[0];
                $placeName = trim((string) ($firstPlace['name'] ?? ''));
                if ($placeName !== '') {
                    $nearbyHint = ' (near ' . $placeName . ')';
                }
            }

            $lines[] = $title . ' - ' . $listingLocation . ' - ₱' . $price . $nearbyHint;
        }

        $locationText = $location !== '' ? ' in ' . $location : '';
        return 'Here are available listings near ' . $landmark . $locationText . ': ' . implode('; ', $lines) . '.';
    }

    private function getRandomListings(int $limit = 5, bool $verifiedOnly = true): array
    {
        try {
            $landListingsModel = new LandListings();
            $builder = $landListingsModel->builder();
            $builder->select('listing_id, title, description, barangay, city, province, price, developing_area, listing_status, is_verified_listing, created_at');

            $db = Database::connect();
            if ($db->tableExists('listing_locations')) {
                $builder->select('listing_locations.latitude AS listing_latitude, listing_locations.longitude AS listing_longitude');
                $builder->join('listing_locations', 'listing_locations.listing_id = land_listings.listing_id', 'left');
            }

            $builder->groupStart();
            $builder->where('listing_status', 'available');
            $builder->orWhere('listing_status', 'Available');
            $builder->groupEnd();

            if ($verifiedOnly) {
                $builder->groupStart();
                $builder->where('is_verified_listing', 'true');
                $builder->orWhere('is_verified_listing', '1');
                $builder->groupEnd();
            }

            $listings = $builder
                ->orderBy('RAND()')
                ->limit($limit)
                ->get()
                ->getResultArray();

            if ($listings === [] && $verifiedOnly) {
                return $this->getRandomListings($limit, false);
            }

            foreach ($listings as &$listing) {
                $listing['primary_image_url'] = $this->getPrimaryImageUrl((int) ($listing['listing_id'] ?? 0));
            }

            return $listings;
        } catch (\Exception $e) {
            log_message('error', 'Get random listings error: ' . $e->getMessage());
            return [];
        }
    }

    private function buildListingDataObject(array $listing, bool $includeSurroundings = false): array
    {
        $coordinates = $this->extractListingCoordinates($listing);
        $isAvailable = $this->isListingAvailable($listing);

        return [
            'id' => (int) ($listing['listing_id'] ?? 0),
            'title' => trim((string) ($listing['title'] ?? 'Untitled Property')),
            'location' => $this->formatLocation($listing),
            'price' => (int) ($listing['price'] ?? 0),
            'size' => (float) ($listing['developing_area'] ?? 0),
            'size_sqm' => (float) ($listing['developing_area'] ?? 0),
            'description' => trim((string) ($listing['description'] ?? '')),
            'availability' => $isAvailable ? 'available' : (string) ($listing['listing_status'] ?? 'unknown'),
            'is_available' => $isAvailable,
            'coordinates' => $coordinates,
            'image' => $listing['primary_image_url'] ?? null,
            'nearby_places' => $includeSurroundings && $coordinates !== null ? $this->fetchNearbyPlacesSummary($coordinates['lat'], $coordinates['lng']) : [],
        ];
    }

    private function extractListingCoordinates(array $listing): ?array
    {
        $latitudeCandidates = [$listing['listing_latitude'] ?? null, $listing['latitude'] ?? null, $listing['Latitude'] ?? null];
        $longitudeCandidates = [$listing['listing_longitude'] ?? null, $listing['longitude'] ?? null, $listing['Longitude'] ?? null];

        $latitude = null;
        foreach ($latitudeCandidates as $candidate) {
            if ($candidate !== null && $candidate !== '' && is_numeric($candidate)) {
                $latitude = (float) $candidate;
                break;
            }
        }

        $longitude = null;
        foreach ($longitudeCandidates as $candidate) {
            if ($candidate !== null && $candidate !== '' && is_numeric($candidate)) {
                $longitude = (float) $candidate;
                break;
            }
        }

        if ($latitude === null || $longitude === null) {
            return null;
        }

        return ['lat' => $latitude, 'lng' => $longitude];
    }

    private function isListingAvailable(array $listing): bool
    {
        return strcasecmp((string) ($listing['listing_status'] ?? ''), 'available') === 0
            && $this->isVerifiedListingValue($listing['is_verified_listing'] ?? null);
    }

    private function isVerifiedListingValue($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return ((int) $value) === 1;
        }

        $normalized = strtolower(trim((string) $value));
        return in_array($normalized, ['true', '1', 'yes', 'verified'], true);
    }

    private function shouldIncludeSurroundings(string $message): bool
    {
        return preg_match('/\b(nearby|surroundings|around|near|neighborhood|neighbourhood|school|beach|church|hospital|restaurant|mall|transport|road access|barangay|brgy)\b/i', $message) === 1;
    }

    private function fetchNearbyPlacesSummary(float $latitude, float $longitude): array
    {
        if ($this->geoapifyApiKey === '') {
            return [];
        }

        $categories = 'education.school,healthcare.hospital,religion.place_of_worship.christianity,beach.beach_resort,catering.restaurant,public_transport.bus';
        $url = 'https://api.geoapify.com/v2/places?categories=' . rawurlencode($categories)
            . '&filter=circle:' . rawurlencode($longitude . ',' . $latitude . ',5000')
            . '&bias=proximity:' . rawurlencode($longitude . ',' . $latitude)
            . '&limit=5&lang=en&apiKey=' . urlencode($this->geoapifyApiKey);

        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8, CURLOPT_CONNECTTIMEOUT => 4, CURLOPT_SSL_VERIFYPEER => true]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($response === false || $httpCode !== 200) {
                return [];
            }

            $body = json_decode((string) $response, true);
            $features = $body['features'] ?? [];
            if (!is_array($features) || $features === []) {
                return [];
            }

            $places = [];
            foreach (array_slice($features, 0, 4) as $feature) {
                $properties = $feature['properties'] ?? [];
                $categoryList = $properties['categories'] ?? [];
                if (!is_array($categoryList)) {
                    $categoryList = [];
                }

                $places[] = [
                    'name' => trim((string) ($properties['name'] ?? $properties['formatted'] ?? 'Nearby place')),
                    'category' => trim((string) ($categoryList[0] ?? 'place')),
                    'formatted' => trim((string) ($properties['formatted'] ?? '')),
                    'distance_m' => isset($properties['distance']) ? (int) $properties['distance'] : null,
                ];
            }

            return $places;
        } catch (\Throwable $e) {
            log_message('error', 'Nearby places lookup error: ' . $e->getMessage());
            return [];
        }
    }

    private function getPrimaryImageUrl(int $listingId): ?string
    {
        try {
            $db = Database::connect();
            if (!$db->tableExists('listing_images')) {
                return null;
            }

            $image = $db->table('listing_images')
                ->select('image_path')
                ->where('listing_id', $listingId)
                ->orderBy('is_primary', 'DESC')
                ->orderBy('image_id', 'ASC')
                ->limit(1)
                ->get()
                ->getRowArray();

            return $image ? base_url('seller/listing-images/' . $image['image_path']) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function formatLocation(array $listing): string
    {
        $parts = array_filter([
            trim((string) ($listing['barangay'] ?? '')),
            trim((string) ($listing['city'] ?? '')),
            trim((string) ($listing['province'] ?? '')),
        ]);

        return $parts !== [] ? implode(', ', $parts) : 'Location unavailable';
    }

    private function extractKeywords(string $message): array
    {
        $commonWords = ['the', 'a', 'an', 'and', 'or', 'but', 'is', 'are', 'am', 'be', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'from', 'that', 'this', 'which', 'who', 'when', 'where', 'why', 'how', 'have', 'has', 'had', 'do', 'does', 'did', 'i', 'you', 'we', 'they', 'my', 'your', 'our', 'their', 'me', 'him', 'her', 'us', 'them', 'can', 'could', 'would', 'should', 'will', 'may', 'must'];

        preg_match_all('/\b\w{4,}\b/i', $message, $matches);
        $words = array_map('strtolower', $matches[0] ?? []);

        return array_values(array_filter($words, function ($word) use ($commonWords) {
            return !in_array(strtolower($word), $commonWords, true);
        }));
    }

    private function buildSystemPrompt(array $listings, string $userMessage = '', ?array $nearbyIntent = null): string
    {
        $listingContext = '';
        if ($listings !== []) {
            $listingContext = "\n\nLISTING_DATA_OBJECT:\n" . json_encode($listings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        }

        $userMessageContext = trim($userMessage) !== '' ? "\n\nUSER_MESSAGE:\n" . $userMessage : '';
        $nearbyContext = $nearbyIntent !== null
            ? "\n\nNEARBY_REQUEST:\n" . json_encode($nearbyIntent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
            : '';

        return "You are Landly AI Assistant, a chatbot designed ONLY for the Landly land marketplace platform.

STRICT RULES:
- Only answer questions related to: Land buying/selling, Land listings, Property details (price, location, size, documents), Seller verification, Legal land process (Philippines), Nearby places (schools, beaches, churches, hospitals), Locations (e.g., Nasugbu, Batangas)
- If question is OUTSIDE this scope: 'Sorry, I can only assist with land listings and property-related inquiries on Landly.'

DATA USAGE:
- Always base answers on provided listing data
- If info missing: 'This information is not available in the current listing data.'
- If a listing is unavailable, say so directly instead of guessing.
- If coordinates are present, use them for location-aware answers.
- If nearby_places are present, use them when answering surroundings questions.
- Prioritize available and verified listings when recommending.

LOCATION HANDLING:
- For surroundings questions: Provide relevant nearby places and keep answers realistic to Philippines (especially Batangas)
- If user asks near a beach, school, barangay/brgy, church, or hospital, recommend listings that best match the location and nearby data.
- For barangay/brgy requests, treat barangay/city/province fields as top signals.

STYLE:
- Short, clear, helpful (max 2-3 sentences per message)
- Simple English or Taglish
- Friendly but professional

RESPONSE FORMAT:
- When suggesting listings, mention property name, location, and key feature
- Mention availability when the user asks whether a listing is available
- Mention surroundings only when the data includes nearby places
- Keep responses concise and engaging" . $userMessageContext . $nearbyContext . $listingContext;
    }

    private function callGeminiApi(string $userMessage, string $systemPrompt): ?string
    {
        try {
            $payload = [
                'contents' => [[ 'parts' => [[ 'text' => $systemPrompt . "\n\nUser: " . $userMessage ]] ]],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 500,
                ]
            ];

            foreach ($this->geminiModels as $model) {
                $url = $this->geminiApiBase . $model . ':generateContent?key=' . urlencode($this->geminiApiKey);
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_CONNECTTIMEOUT => 5,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode($payload),
                    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                    CURLOPT_SSL_VERIFYPEER => true,
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if (curl_errno($ch)) {
                    $this->recordGeminiError(0, $model, 'cURL error: ' . curl_error($ch));
                    continue;
                }

                if ($httpCode === 404) {
                    log_message('error', "Gemini model not available: {$model}");
                    $this->recordGeminiError($httpCode, $model, (string) $response);
                    continue;
                }

                if ($httpCode !== 200) {
                    log_message('error', "Gemini API returned status {$httpCode} for {$model}: {$response}");
                    $this->recordGeminiError($httpCode, $model, (string) $response);
                    continue;
                }

                $body = json_decode((string) $response, true);
                if (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
                    return trim((string) $body['candidates'][0]['content']['parts'][0]['text']);
                }

                log_message('error', 'Invalid Gemini API response structure: ' . json_encode($body));
                $this->recordGeminiError($httpCode, $model, (string) $response);
            }

            return null;
        } catch (\Exception $e) {
            log_message('error', 'Chatbot API error: ' . $e->getMessage());
            return null;
        }
    }

    private function buildGeminiFallbackMessage(): string
    {
        if (empty($this->geminiLastError)) {
            return 'I can still suggest matching listings right now, but the AI reply is temporarily unavailable. Please try again shortly.';
        }

        $code = (int) ($this->geminiLastError['code'] ?? 0);
        $errorBody = json_decode((string) ($this->geminiLastError['message'] ?? ''), true);
        $errorMessage = (string) ($errorBody['error']['message'] ?? '');

        if ($code === 429 || str_contains($errorMessage, 'quota')) {
            return 'Gemini quota is exhausted for the current API key. Enable billing, increase quota, or try again after the reset window.';
        }

        if ($code === 401 || $code === 403) {
            return 'Gemini rejected the current API key. Check that the key is valid and that the Gemini API is enabled for the project.';
        }

        if ($code === 404) {
            return 'Gemini could not find a usable model for this key. Try a different Gemini model or verify model access in Google AI Studio.';
        }

        return 'I can still suggest matching listings right now, but the AI reply is temporarily unavailable. Please try again shortly.';
    }

    private function isAvailabilityQuestion(string $message): bool
    {
        return preg_match('/\b(available|availability|for sale|open)\b/i', $message) === 1
            && preg_match('/\b(property|properties|land|listing|listings)\b/i', $message) === 1;
    }

    private function buildAvailabilityReply(array $listingDataObjects): string
    {
        if ($listingDataObjects === []) {
            return 'I checked the listings and there are no available properties to show right now. You can try a specific location like Nasugbu, Lian, or Calatagan and I will search again.';
        }

        $lines = [];
        foreach (array_slice($listingDataObjects, 0, 3) as $listing) {
            $title = trim((string) ($listing['title'] ?? 'Property'));
            $location = trim((string) ($listing['location'] ?? 'Location unavailable'));
            $price = isset($listing['price']) ? number_format((int) $listing['price']) : 'N/A';
            $lines[] = $title . ' - ' . $location . ' - ₱' . $price;
        }

        return 'These are available properties right now: ' . implode('; ', $lines) . '.';
    }

    private function recordGeminiError(int $code, string $model, string $message): void
    {
        $priority = 0;

        if ($code === 429) {
            $priority = 3;
        } elseif ($code === 401 || $code === 403) {
            $priority = 2;
        } elseif ($code === 404) {
            $priority = 1;
        }

        $currentPriority = (int) ($this->geminiLastError['priority'] ?? -1);
        if ($priority < $currentPriority) {
            return;
        }

        $this->geminiLastError = [
            'priority' => $priority,
            'code' => $code,
            'model' => $model,
            'message' => $message,
        ];
    }


    private function getLandListingDetailsVerified(array $listingDataObjects): array
    {
        $details = [];
        foreach ($listingDataObjects as $listing) {
            if (isset($listing['id'], $listing['title'], $listing['location'], $listing['price'], $listing['size'], $listing['description'], $listing['availability'])) {
                $details[] = [
                    'id' => (int) ($listing['id'] ?? 0),
                    'title' => trim((string) ($listing['title'] ?? 'Untitled Property')),
                    'location' => trim((string) ($listing['location'] ?? 'Location unavailable')),
                    'price' => (int) ($listing['price'] ?? 0),
                    'size' => (float) ($listing['size'] ?? 0),
                    'description' => trim((string) ($listing['description'] ?? '')),
                    'availability' => trim((string) ($listing['availability'] ?? 'unknown')),
                ];
            }
        }
        return $details;
    }
}
