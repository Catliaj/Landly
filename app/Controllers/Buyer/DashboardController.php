<?php

namespace App\Controllers\Buyer;

use App\Controllers\BaseController;
use App\Models\LandListings;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;


class DashboardController extends BaseController
{
    public function index(): ResponseInterface|string
    {
        $userId = $this->getCurrentUserId();
        $userProfile = $this->getCurrentUserProfile($userId);
        $Fullname = (string) ($userProfile['full_name'] ?? $this->getCurrentUserFullName());

        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ]);
        }

        [$browseListings, $browsePropertyData] = $this->getBrowseListingsPayload();
        $buyerInquiries = $this->getBuyerInquiriesPayload($userId);
        $sidebarCounts = $this->getBuyerSidebarCounts($userId);

        return view('Pages/Buyer/Dashboard_Buyer', [
            'fullname' => $Fullname,
            'userProfile' => $userProfile,
            'buyerProfile' => $this->getBuyerProfilePayload($userId),
            'browseListings' => $browseListings,
            'browsePropertyData' => $browsePropertyData,
            'buyerInquiries' => $buyerInquiries,
            'sidebarCounts' => $sidebarCounts,
            'geoapifyApiKey' => $this->resolveGeoapifyApiKey(),
        ]);
    }

    private function resolveGeoapifyApiKey(): string
    {
        $candidates = [
            env('GEOAPIFY_API_KEY'),
            $_ENV['GEOAPIFY_API_KEY'] ?? null,
            $_SERVER['GEOAPIFY_API_KEY'] ?? null,
            getenv('GEOAPIFY_API_KEY') ?: null,
        ];

        foreach ($candidates as $value) {
            $key = trim((string) ($value ?? ''));
            if ($key !== '') {
                return $key;
            }
        }
        return '';
    }

    public function sidebarCounts(): ResponseInterface
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Invalid request.',
            ]);
        }

        $userId = $this->getCurrentUserId();
        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'counts' => $this->getBuyerSidebarCounts($userId),
        ]);
    }

    private function getCurrentUserProfile(int $userId): array
    {
        if ($userId <= 0) {
            return [
                'full_name' => 'Buyer',
                'email' => 'N/A',
                'avatar_url' => '',
                'initials' => 'NA',
                'status_label' => 'Inactive Buyer',
                'status_class' => 'inactive',
            ];
        }

        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId) ?? [];

        $fullName = trim((string) (($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
        $fullName = $fullName !== '' ? $fullName : 'Buyer';
        $isActive = (int) ($user['is_active'] ?? 0) === 1;

        return [
            'full_name' => $fullName,
            'email' => trim((string) ($user['email'] ?? 'N/A')),
            'avatar_url' => $this->resolveUserProfilePictureUrl((string) ($user['profile_picture'] ?? '')),
            'initials' => $this->formatInitials($fullName),
            'status_label' => $isActive ? 'Active Buyer' : 'Inactive Buyer',
            'status_class' => $isActive ? 'active' : 'inactive',
        ];
    }

    private function getBuyerProfilePayload(int $buyerId): array
    {
        $userModel = new UserModel();
        $user = $userModel->find($buyerId) ?? [];

        $fullName = trim((string) (($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
        $fullName = $fullName !== '' ? $fullName : 'Buyer';
        $isActive = (int) ($user['is_active'] ?? 0) === 1;

        return [
            'full_name' => $fullName,
            'email' => trim((string) ($user['email'] ?? 'N/A')),
            'avatar_url' => $this->resolveUserProfilePictureUrl((string) ($user['profile_picture'] ?? '')),
            'initials' => $this->formatInitials($fullName),
            'first_name' => trim((string) ($user['first_name'] ?? '')),
            'last_name' => trim((string) ($user['last_name'] ?? '')),
            'status_label' => $isActive ? 'Active Buyer' : 'Inactive Buyer',
            'status_class' => $isActive ? 'active' : 'inactive',
            'stats' => $this->getBuyerProfileStats($buyerId),
        ];
    }

    private function getBuyerProfileStats(int $buyerId): array
    {
        $db = Database::connect();

        $savedProperties = $db->table('buyer_favorites')
            ->where('buyer_id', $buyerId)
            ->countAllResults();

        $acceptedInquiries = $db->table('inquiries')
            ->where('buyer_id', $buyerId)
            ->where('inquiry_status', 'accepted')
            ->countAllResults();

        $unreadMessages = $db->table('messages m')
            ->select('COUNT(*) AS total_unread')
            ->join('message_sessions ms', 'ms.session_id = m.session_id', 'inner')
            ->groupStart()
            ->where('ms.buyer_id', $buyerId)
            ->orWhere('ms.seller_id', $buyerId)
            ->groupEnd()
            ->where('m.sender_id !=', $buyerId)
            ->where('m.is_read', 0)
            ->get()
            ->getRowArray();

        return [
            'saved_properties' => (int) $savedProperties,
            'accepted_inquiries' => (int) $acceptedInquiries,
            'unread_messages' => (int) ($unreadMessages['total_unread'] ?? 0),
        ];
    }

    private function resolveUserProfilePictureUrl(string $profilePicture): string
    {
        $profilePicture = trim($profilePicture);
        if ($profilePicture === '') {
            return '';
        }

        if (preg_match('#^(?:https?:)?//#i', $profilePicture) === 1 || str_starts_with($profilePicture, 'data:')) {
            return $profilePicture;
        }

        return base_url('media/profile?path=' . rawurlencode($profilePicture));
    }

    private function getBuyerSidebarCounts(int $buyerId): array
    {
        $db = Database::connect();

        $savedProperties = $db->table('buyer_favorites')
            ->where('buyer_id', $buyerId)
            ->countAllResults();

        $acceptedInquiries = $db->table('inquiries')
            ->where('buyer_id', $buyerId)
            ->where('inquiry_status', 'accepted')
            ->countAllResults();

        $unreadMessages = $db->table('messages m')
            ->select('COUNT(*) AS total_unread')
            ->join('message_sessions ms', 'ms.session_id = m.session_id', 'inner')
            ->groupStart()
            ->where('ms.buyer_id', $buyerId)
            ->orWhere('ms.seller_id', $buyerId)
            ->groupEnd()
            ->where('m.sender_id !=', $buyerId)
            ->where('m.is_read', 0)
            ->get()
            ->getRowArray();

        return [
            'saved_properties' => (int) $savedProperties,
            'accepted_inquiries' => (int) $acceptedInquiries,
            'unread_messages' => (int) ($unreadMessages['total_unread'] ?? 0),
        ];
    }

    private function getCurrentUserFullName(): string
    {
        $userId = $this->getCurrentUserId();
        if ($userId <= 0) {
            return '';
        }

        $userModel = new \App\Models\UserModel();
        return $userModel->getFullnameById($userId) ?? '';
    }

    private function getBrowseListingsPayload(): array
    {
        $listingModel = new LandListings();
        $rows = $listingModel
            ->select('land_listings.*, users.first_name, users.last_name, users.email, users.created_at AS seller_created_at, listing_locations.latitude AS listing_latitude, listing_locations.longitude AS listing_longitude')
            ->join('users', 'users.user_id = land_listings.seller_id', 'left')
            ->join('listing_locations', 'listing_locations.listing_id = land_listings.listing_id', 'left')
            ->where('land_listings.is_verified_listing', 'true')
            ->orderBy('land_listings.created_at', 'DESC')
            ->findAll();

        if ($rows === []) {
            return [[], []];
        }

        $listingIds = array_values(array_filter(array_map(
            static fn(array $row): int => (int) ($row['listing_id'] ?? 0),
            $rows
        )));

        $sellerIds = array_values(array_filter(array_map(
            static fn(array $row): int => (int) ($row['seller_id'] ?? 0),
            $rows
        )));

        $primaryImages = $this->getPrimaryImagesByListing($listingIds);
        $sellerListingCounts = $this->getSellerListingCounts($sellerIds);

        $browseListings = [];
        $browsePropertyData = [];

        foreach ($rows as $row) {
            $listingId = (int) ($row['listing_id'] ?? 0);
            if ($listingId <= 0) {
                continue;
            }

            $statusMeta = $this->getListingStatusMeta((string) ($row['listing_status'] ?? ''));
            $title = trim((string) ($row['title'] ?? 'Untitled Listing'));
            $imageUrl = $this->resolveListingImageUrl($primaryImages[$listingId] ?? null, $title);
            $locationLabel = $this->formatLocation($row);
            $propertyTypeLabel = $this->formatPropertyType((string) ($row['property_type'] ?? ''));
            $documentStatusLabel = $this->formatDocumentStatus($row);
            $roadAccessLabel = $this->formatRoadAccess((string) ($row['road_access_type'] ?? ''));
            $viewTypeLabel = $this->formatViewType((string) ($row['view_type'] ?? ''));
            $sellerName = $this->formatSellerName($row);
            $sellerInitials = $this->formatInitials($sellerName);
            $areaRaw = trim((string) ($row['developing_area'] ?? ''));
            $areaLabel = $areaRaw !== '' ? $areaRaw . ' sqm' : 'N/A';
            $priceValue = (float) ($row['price'] ?? 0);
            $areaNumeric = is_numeric($areaRaw) ? (float) $areaRaw : 0.0;
            $pricePerSqm = $areaNumeric > 0 ? $this->formatPeso($priceValue / $areaNumeric) . ' / sqm' : 'N/A';

            $browseListings[] = [
                'listing_id' => $listingId,
                'title' => $title,
                'location_label' => $locationLabel,
                'property_type_label' => $propertyTypeLabel,
                'document_status_label' => $documentStatusLabel,
                'road_access_label' => $roadAccessLabel,
                'status_label' => $statusMeta['label'],
                'status_class' => $statusMeta['class'],
                'price_label' => $this->formatPeso((float) ($row['price'] ?? 0)),
                'seller_name' => $sellerName,
                'seller_initials' => $sellerInitials,
                'image_url' => $imageUrl,
            ];

            $browsePropertyData[$listingId] = [
                'title' => $title,
                'listingId' => $listingId,
                'price' => $this->formatPeso($priceValue),
                'pricePerSqm' => $pricePerSqm,
                'area' => $areaLabel,
                'type' => $propertyTypeLabel,
                'titleStatus' => $documentStatusLabel,
                'location' => $locationLabel,
                'address' => $locationLabel,
                'barangay' => trim((string) ($row['barangay'] ?? '')),
                'city' => trim((string) ($row['city'] ?? '')),
                'province' => trim((string) ($row['province'] ?? '')),
                'roadAccess' => $roadAccessLabel,
                'viewType' => $viewTypeLabel,
                'investmentReady' => $this->formatBooleanLabel($row['investment_ready'] ?? null),
                'isTitled' => $this->formatBooleanLabel($row['is_titled'] ?? null),
                'hasTaxDeclaration' => $this->formatBooleanLabel($row['has_tax_declaration'] ?? null),
                'hasLraApprovedPlan' => $this->formatBooleanLabel($row['has_lra_approved_plan'] ?? null),
                'motherTitleDisclosed' => $this->formatBooleanLabel($row['mother_titled_disclosed'] ?? null),
                'documentStatus' => $documentStatusLabel,
                'listingStatus' => $statusMeta['label'],
                'mapAddress' => $locationLabel !== 'Location unavailable' ? $locationLabel . ', Philippines' : 'Nasugbu, Batangas, Philippines',
                'coordinates' => [
                    'lat' => $this->normalizeCoordinateValue($row['listing_latitude'] ?? null),
                    'lng' => $this->normalizeCoordinateValue($row['listing_longitude'] ?? null),
                ],
                'images' => [$imageUrl],
                'description' => trim((string) ($row['description'] ?? 'No description provided.')),
                'features' => $this->buildFeatureTags($row),
                'seller' => [
                    'name' => $sellerName,
                    'initials' => $sellerInitials,
                    'phone' => 'N/A',
                    'email' => trim((string) ($row['email'] ?? 'N/A')),
                    'verified' => true,
                    'listings' => (int) ($sellerListingCounts[(int) ($row['seller_id'] ?? 0)] ?? 0),
                    'memberSince' => $this->formatMemberSince((string) ($row['seller_created_at'] ?? '')),
                ],
            ];
        }

        return [$browseListings, $browsePropertyData];
    }

    private function getPrimaryImagesByListing(array $listingIds): array
    {
        if ($listingIds === []) {
            return [];
        }

        $db = Database::connect();
        if (! $db->tableExists('listing_images')) {
            return [];
        }

        $rows = $db->table('listing_images')
            ->select('listing_id, image_path, is_primary, image_id')
            ->whereIn('listing_id', $listingIds)
            ->orderBy('is_primary', 'DESC')
            ->orderBy('image_id', 'ASC')
            ->get()
            ->getResultArray();

        $images = [];

        foreach ($rows as $row) {
            $listingId = (int) ($row['listing_id'] ?? 0);
            if ($listingId <= 0 || isset($images[$listingId])) {
                continue;
            }

            $images[$listingId] = (string) ($row['image_path'] ?? '');
        }

        return $images;
    }

    private function getSellerListingCounts(array $sellerIds): array
    {
        if ($sellerIds === []) {
            return [];
        }

        $db = Database::connect();
        $rows = $db->table('land_listings')
            ->select('seller_id, COUNT(*) AS total_listings')
            ->whereIn('seller_id', $sellerIds)
            ->groupBy('seller_id')
            ->get()
            ->getResultArray();

        $counts = [];
        foreach ($rows as $row) {
            $sellerId = (int) ($row['seller_id'] ?? 0);
            if ($sellerId <= 0) {
                continue;
            }

            $counts[$sellerId] = (int) ($row['total_listings'] ?? 0);
        }

        return $counts;
    }

    private function getListingStatusMeta(string $status): array
    {
        return match ($status) {
            'available' => ['label' => 'Available', 'class' => 'available'],
            'in_inquiry' => ['label' => 'In Inquiry', 'class' => 'inquiry'],
            'reserved' => ['label' => 'Reserved', 'class' => 'reserved'],
            'closed' => ['label' => 'Closed', 'class' => 'closed'],
            default => ['label' => 'Available', 'class' => 'available'],
        };
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

    private function normalizeCoordinateValue(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function formatPropertyType(string $propertyType): string
    {
        return match ($propertyType) {
            'agricultural_land' => 'Agricultural',
            'commercial_land' => 'Commercial',
            'residential_land' => 'Residential',
            default => 'Unspecified',
        };
    }

    private function formatDocumentStatus(array $listing): string
    {
        if (! empty($listing['is_titled'])) {
            return 'Clean Title';
        }

        if (! empty($listing['has_tax_declaration'])) {
            return 'Tax Declaration';
        }

        return match ((string) ($listing['document_status'] ?? 'pending')) {
            'complete' => 'Documents Complete',
            'partial' => 'Documents Partial',
            default => 'Documents Pending',
        };
    }

    private function formatRoadAccess(string $roadAccess): string
    {
        return match ($roadAccess) {
            'cemented' => 'Cemented Road',
            'right_of_way' => 'Right of Way',
            'none' => 'No Road Access',
            default => 'Road Access N/A',
        };
    }

    private function formatViewType(string $viewType): string
    {
        return match ($viewType) {
            'mountain', 'mountain_view' => 'Mountain View',
            'sea', 'sea_view', 'ocean_view' => 'Sea View',
            'city', 'city_view' => 'City View',
            'farm', 'farm_view' => 'Farm View',
            default => $viewType !== '' ? ucwords(str_replace('_', ' ', $viewType)) : 'Not specified',
        };
    }

    private function formatBooleanLabel(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        $normalized = strtolower(trim((string) $value));
        return in_array($normalized, ['1', 'true', 'yes', 'y'], true) ? 'Yes' : 'No';
    }

    private function formatSellerName(array $listing): string
    {
        $fullName = trim((string) (($listing['first_name'] ?? '') . ' ' . ($listing['last_name'] ?? '')));
        return $fullName !== '' ? $fullName : 'Unknown Seller';
    }

    private function formatInitials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $parts = array_filter($parts);

        if ($parts === []) {
            return 'NA';
        }

        $first = mb_substr((string) ($parts[0] ?? ''), 0, 1);
        $second = mb_substr((string) ($parts[1] ?? ''), 0, 1);

        return strtoupper($first . $second);
    }

    private function formatPeso(float $amount): string
    {
        return '₱' . number_format($amount, 2);
    }

    private function formatMemberSince(string $rawDate): string
    {
        if ($rawDate === '') {
            return 'N/A';
        }

        $timestamp = strtotime($rawDate);
        if ($timestamp === false) {
            return 'N/A';
        }

        return date('M Y', $timestamp);
    }

    private function buildFeatureTags(array $listing): array
    {
        $features = [];

        $propertyType = $this->formatPropertyType((string) ($listing['property_type'] ?? ''));
        if ($propertyType !== 'Unspecified') {
            $features[] = $propertyType . ' Land';
        }

        $roadAccess = $this->formatRoadAccess((string) ($listing['road_access_type'] ?? ''));
        if ($roadAccess !== 'Road Access N/A') {
            $features[] = $roadAccess;
        }

        if (! empty($listing['is_titled'])) {
            $features[] = 'Titled Property';
        }

        if (! empty($listing['has_tax_declaration'])) {
            $features[] = 'With Tax Declaration';
        }

        if (! empty($listing['has_lra_approved_plan'])) {
            $features[] = 'LRA Approved Plan';
        }

        if (! empty($listing['investment_ready'])) {
            $features[] = 'Investment Ready';
        }

        if ($features === []) {
            return ['Verified Listing'];
        }

        return array_values(array_unique($features));
    }

    private function resolveListingImageUrl(?string $imagePath, string $title): string
    {
        $imagePath = trim((string) $imagePath);

        if ($imagePath !== '') {
            if (preg_match('#^(?:https?:)?//#i', $imagePath) === 1 || str_starts_with($imagePath, 'data:')) {
                return $imagePath;
            }

            return base_url(ltrim(str_replace('\\', '/', $imagePath), '/'));
        }

        $label = trim($title) !== '' ? trim($title) : 'Landly Listing';
        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="480" viewBox="0 0 800 480"><rect width="800" height="480" fill="#183127"/><rect x="36" y="36" width="728" height="408" rx="30" fill="#234236"/><text x="50%%" y="50%%" text-anchor="middle" dominant-baseline="middle" fill="#d2b48c" font-family="Arial, sans-serif" font-size="34">%s</text></svg>',
            htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
        );

        return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
    }

    private function getBuyerInquiriesPayload(int $buyerId): array
    {
        $db = Database::connect();

        $rows = $db->table('inquiries i')
            ->select('i.inquiry_id, i.listing_id, i.seller_id, i.inquiry_status, i.created_at, i.updated_at, l.title, l.price, ms.session_id')
            ->join('land_listings l', 'l.listing_id = i.listing_id', 'left')
            ->join('message_sessions ms', 'ms.inquiry_id = i.inquiry_id', 'left')
            ->where('i.buyer_id', $buyerId)
            ->orderBy('i.created_at', 'DESC')
            ->get()
            ->getResultArray();

        if ($rows === []) {
            return [];
        }

        $listingIds = array_values(array_filter(array_map(
            static fn(array $row): int => (int) ($row['listing_id'] ?? 0),
            $rows
        )));

        $primaryImages = $this->getPrimaryImagesByListing($listingIds);
        $inquiries = [];

        foreach ($rows as $row) {
            $listingId = (int) ($row['listing_id'] ?? 0);
            $title = trim((string) ($row['title'] ?? 'Untitled Listing'));
            $status = strtolower(trim((string) ($row['inquiry_status'] ?? 'pending')));

            $inquiries[] = [
                'inquiry_id' => (int) ($row['inquiry_id'] ?? 0),
                'listing_id' => $listingId,
                'session_id' => (int) ($row['session_id'] ?? 0),
                'title' => $title,
                'price_label' => $this->formatPeso((float) ($row['price'] ?? 0)),
                'image_url' => $this->resolveListingImageUrl($primaryImages[$listingId] ?? null, $title),
                'status_label' => $this->formatInquiryStatusLabel($status),
                'status_class' => $this->formatInquiryStatusClass($status),
                'message_preview' => $this->formatInquiryMessagePreview($status),
                'date_label' => $this->formatInquiryDateLabel((string) ($row['created_at'] ?? ''), (string) ($row['updated_at'] ?? '')),
            ];
        }

        return $inquiries;
    }

    private function formatInquiryStatusLabel(string $status): string
    {
        return match ($status) {
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
            'reserved' => 'Reserved',
            'closed' => 'Closed',
            default => 'Pending',
        };
    }

    private function formatInquiryStatusClass(string $status): string
    {
        return match ($status) {
            'accepted', 'reserved', 'closed' => 'replied',
            'rejected' => 'viewed',
            default => 'pending',
        };
    }

    private function formatInquiryMessagePreview(string $status): string
    {
        return match ($status) {
            'accepted' => 'Your inquiry has been accepted by the seller.',
            'rejected' => 'Your inquiry was reviewed by the seller.',
            'reserved' => 'This inquiry is now marked as reserved.',
            'closed' => 'This inquiry thread has been closed.',
            default => 'Inquiry submitted. Waiting for seller response.',
        };
    }

    private function formatInquiryDateLabel(string $createdAt, string $updatedAt): string
    {
        $createdLabel = $this->formatDateTimeShort($createdAt);
        $updatedLabel = $this->formatDateTimeShort($updatedAt);

        if ($createdLabel === 'N/A') {
            return 'Date unavailable';
        }

        if ($updatedLabel !== 'N/A' && $updatedAt !== '' && $updatedAt !== $createdAt) {
            return 'Sent ' . $createdLabel . ' • Updated ' . $updatedLabel;
        }

        return 'Sent ' . $createdLabel;
    }

    private function formatDateTimeShort(string $rawDate): string
    {
        if ($rawDate === '') {
            return 'N/A';
        }

        $timestamp = strtotime($rawDate);
        if ($timestamp === false) {
            return 'N/A';
        }

        return date('M d, Y g:i A', $timestamp);
    }


}
