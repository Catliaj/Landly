<?php

namespace App\Controllers\Buyer;

use App\Controllers\BaseController;
use App\Models\LandListings;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;


class DashboardController extends BaseController
{
    private const NASUGBU_BARANGAYS = [
        'Papaya',
        'Looc',
        'Bulihan',
        'Calayo',
        'Butucan',
        'Balaytigui',
        'Latag',
        'Utod',
        'Natipuan',
        'Mataas Na Pulo',
        'Malapad Na Bato',
        'Kayrilaw',
        'Bunducan',
        'Dayap',
        'Munting Indang',
        'Maugat',
        'Aga',
        'Pantalan',
        'Putat',
        'Tumalim',
        'Wawa',
        'Catandaan',
        'Talangan',
        'Barangay 2',
        'Barangay 1',
        'Barangay 5',
        'Barangay 4',
        'Barangay 3',
        'Kaylaway',
        'Barangay 10',
        'Barangay 9',
        'Barangay 8',
        'Barangay 7',
        'Barangay 6',
        'Barangay 11',
        'Barangay 12',
        'Banilad',
        'Cogunan',
        'Bucana',
        'Reparo',
        'Lumbangan',
        'Bilaran',
    ];

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

        [$browseListings, $browsePropertyData] = $this->getBrowseListingsPayload($userId);
        $browseFilterOptions = $this->getBrowseFilterOptions();
        $buyerInquiries = $this->getBuyerInquiriesPayload($userId);
        $sidebarCounts = $this->getBuyerSidebarCounts($userId);

        return view('Pages/Buyer/Dashboard_Buyer', [
            'fullname' => $Fullname,
            'userProfile' => $userProfile,
            'buyerProfile' => $this->getBuyerProfilePayload($userId),
            'browseListings' => $browseListings,
            'browsePropertyData' => $browsePropertyData,
            'browseFilterOptions' => $browseFilterOptions,
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

    public function filterListings(): ResponseInterface
    {
        $buyerId = $this->getCurrentUserId();
        if ($buyerId <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ]);
        }

        $filters = [
            'barangay' => trim((string) $this->request->getGet('barangay')),
            'min_price' => trim((string) $this->request->getGet('min_price')),
            'max_price' => trim((string) $this->request->getGet('max_price')),
            'min_size' => trim((string) $this->request->getGet('min_size')),
            'max_size' => trim((string) $this->request->getGet('max_size')),
            'property_type' => trim((string) $this->request->getGet('property_type')),
            'road_access' => trim((string) $this->request->getGet('road_access')),
            'view_type' => trim((string) $this->request->getGet('view_type')),
            'sort' => trim((string) $this->request->getGet('sort')),
            'page' => trim((string) $this->request->getGet('page')),
            'per_page' => trim((string) $this->request->getGet('per_page')),
        ];

        [$listings, $propertyData, $pagination] = $this->getFilteredBrowseListingsPayload($buyerId, $filters);

        return $this->response->setJSON([
            'status' => 'success',
            'total' => (int) ($pagination['total'] ?? 0),
            'page' => (int) ($pagination['page'] ?? 1),
            'per_page' => (int) ($pagination['per_page'] ?? 12),
            'total_pages' => (int) ($pagination['total_pages'] ?? 1),
            'listings' => $listings,
            'property_data' => $propertyData,
            'filters' => $pagination['filters'] ?? [],
        ]);
    }

    public function trackListingView(): ResponseInterface
    {
        $buyerId = $this->getCurrentUserId();
        if ($buyerId <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ]);
        }

        $listingId = (int) ($this->request->getPost('listing_id') ?? 0);
        if ($listingId <= 0) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'Invalid listing id.',
            ]);
        }

        $db = Database::connect();
        if (! $db->tableExists('listing_daily_views')) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'listing_daily_views table is missing. Run migrations first.',
            ]);
        }

        $listingExists = $db->table('land_listings')
            ->select('listing_id')
            ->where('listing_id', $listingId)
            ->limit(1)
            ->get()
            ->getRowArray();

        if (! $listingExists) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Listing not found.',
            ]);
        }

        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');

        $db->transStart();

        $db->table('listing_daily_views')
            ->ignore(true)
            ->insert([
                'listing_id' => $listingId,
                'viewer_user_id' => $buyerId,
                'view_date' => $today,
                'created_at' => $now,
            ]);

        $wasInserted = $db->affectedRows() > 0;

        if (! $wasInserted) {
            $db->transComplete();
            return $this->response->setJSON([
                'status' => 'success',
                'counted' => false,
                'message' => 'View already recorded for today.',
            ]);
        }

        $analyticsRow = $db->table('listing_analytics')
            ->select('analytics_id')
            ->where('listing_id', $listingId)
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($analyticsRow) {
            $db->table('listing_analytics')
                ->set('total_views', 'total_views + 1', false)
                ->set('last_viewed_at', $now)
                ->where('listing_id', $listingId)
                ->update();
        } else {
            $db->table('listing_analytics')->insert([
                'listing_id' => $listingId,
                'total_views' => 1,
                'total_inquiries' => 0,
                'total_reservations' => 0,
                'total_closed' => 0,
                'last_viewed_at' => $now,
            ]);
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Unable to record listing view.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'counted' => true,
            'message' => 'View recorded.',
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
            'status_label' => $isActive ? 'Buyer' : 'Inactive Buyer',
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
            'status_label' => $isActive ? 'Buyer' : 'Inactive Buyer',
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

    private function getBrowseListingsPayload(int $buyerId): array
    {
        $listingModel = new LandListings();
        $rows = $listingModel
            ->select('land_listings.*, users.first_name, users.last_name, users.email, users.created_at AS seller_created_at, listing_locations.latitude AS listing_latitude, listing_locations.longitude AS listing_longitude')
            ->join('users', 'users.user_id = land_listings.seller_id', 'left')
            ->join('listing_locations', 'listing_locations.listing_id = land_listings.listing_id', 'left')
            ->where('land_listings.is_verified_listing', 'true')
            ->orderBy('land_listings.created_at', 'DESC')
            ->findAll();

        $rows = array_values(array_filter($rows, fn(array $row): bool => $this->isNasugbuListing($row)));

        if ($rows === []) {
            return [[], []];
        }

        $listingIds = array_values(array_filter(array_map(
            static fn(array $row): int => (int) ($row['listing_id'] ?? 0),
            $rows
        )));

        $favoriteListingIds = [];
        if ($buyerId > 0 && $listingIds !== []) {
            $favoriteRows = Database::connect()->table('buyer_favorites')
                ->select('listing_id')
                ->where('buyer_id', $buyerId)
                ->whereIn('listing_id', $listingIds)
                ->get()
                ->getResultArray();

            $favoriteListingIds = array_fill_keys(array_map(
                static fn(array $row): int => (int) ($row['listing_id'] ?? 0),
                $favoriteRows
            ), true);
        }

        $sellerIds = array_values(array_filter(array_map(
            static fn(array $row): int => (int) ($row['seller_id'] ?? 0),
            $rows
        )));

        $primaryImages = $this->getPrimaryImagesByListing($listingIds);
        $listingImagePaths = $this->getListingImagesByListing($listingIds);
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
            $propertyTypeKey = strtolower(trim((string) ($row['property_type'] ?? '')));
            $imageUrl = $this->resolveListingImageUrl($primaryImages[$listingId] ?? null, $title);
            $resolvedImages = [];
            foreach (($listingImagePaths[$listingId] ?? []) as $imagePath) {
                $resolvedImages[] = $this->resolveListingImageUrl((string) $imagePath, $title);
            }
            $resolvedImages = array_values(array_unique(array_filter($resolvedImages, static fn(string $url): bool => trim($url) !== '')));
            if ($resolvedImages === []) {
                $resolvedImages = [$imageUrl];
            }
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
                'is_saved' => isset($favoriteListingIds[$listingId]),
                'property_type_key' => $propertyTypeKey,
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
                'images' => $resolvedImages,
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

    private function getFilteredBrowseListingsPayload(int $buyerId, array $filters, int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(20, $perPage));

        $listingModel = new LandListings();
        $builder = $listingModel->builder();
        $builder->select('land_listings.*, users.first_name, users.last_name, users.email, users.created_at AS seller_created_at, listing_locations.latitude AS listing_latitude, listing_locations.longitude AS listing_longitude')
            ->join('users', 'users.user_id = land_listings.seller_id', 'left')
            ->join('listing_locations', 'listing_locations.listing_id = land_listings.listing_id', 'left');

        $normalizedFilters = $this->normalizeBrowseFilters($filters);
        $this->applyBrowseFiltersToBuilder($builder, $normalizedFilters);

        $countBuilder = clone $builder;
        $total = (int) $countBuilder->countAllResults();

        $sortColumn = 'land_listings.created_at';
        $sortDirection = 'DESC';
        switch ($normalizedFilters['sort']) {
            case 'price_asc':
                $sortColumn = 'land_listings.price';
                $sortDirection = 'ASC';
                break;
            case 'price_desc':
                $sortColumn = 'land_listings.price';
                $sortDirection = 'DESC';
                break;
            case 'largest_lot':
                $sortColumn = 'land_listings.developing_area';
                $sortDirection = 'DESC';
                break;
            case 'newest':
            default:
                $sortColumn = 'land_listings.created_at';
                $sortDirection = 'DESC';
                break;
        }

        $rows = [];
        if ($total > 0) {
            $rows = $builder
                ->orderBy($sortColumn, $sortDirection)
                ->limit($perPage, ($page - 1) * $perPage)
                ->get()
                ->getResultArray();
        }

        $listingIds = array_values(array_filter(array_map(
            static fn(array $row): int => (int) ($row['listing_id'] ?? 0),
            $rows
        )));

        $favoriteListingIds = [];
        if ($buyerId > 0 && $listingIds !== []) {
            $favoriteRows = Database::connect()->table('buyer_favorites')
                ->select('listing_id')
                ->where('buyer_id', $buyerId)
                ->whereIn('listing_id', $listingIds)
                ->get()
                ->getResultArray();

            $favoriteListingIds = array_fill_keys(array_map(
                static fn(array $row): int => (int) ($row['listing_id'] ?? 0),
                $favoriteRows
            ), true);
        }

        $sellerIds = array_values(array_filter(array_map(
            static fn(array $row): int => (int) ($row['seller_id'] ?? 0),
            $rows
        )));

        $primaryImages = $this->getPrimaryImagesByListing($listingIds);
        $listingImagePaths = $this->getListingImagesByListing($listingIds);
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
            $propertyTypeKey = strtolower(trim((string) ($row['property_type'] ?? '')));
            $imageUrl = $this->resolveListingImageUrl($primaryImages[$listingId] ?? null, $title);
            $resolvedImages = [];
            foreach (($listingImagePaths[$listingId] ?? []) as $imagePath) {
                $resolvedImages[] = $this->resolveListingImageUrl((string) $imagePath, $title);
            }
            $resolvedImages = array_values(array_unique(array_filter($resolvedImages, static fn(string $url): bool => trim($url) !== '')));
            if ($resolvedImages === []) {
                $resolvedImages = [$imageUrl];
            }
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
                'is_saved' => isset($favoriteListingIds[$listingId]),
                'property_type_key' => $propertyTypeKey,
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
                'images' => $resolvedImages,
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

        $totalPages = max(1, (int) ceil(max(0, $total) / $perPage));

        return [
            $browseListings,
            $browsePropertyData,
            [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => $totalPages,
                'filters' => $normalizedFilters,
            ],
        ];
    }

    private function normalizeBrowseFilters(array $filters): array
    {
        return [
            'barangay' => trim((string) ($filters['barangay'] ?? '')),
            'min_price' => is_numeric($filters['min_price'] ?? null) ? (float) $filters['min_price'] : null,
            'max_price' => is_numeric($filters['max_price'] ?? null) ? (float) $filters['max_price'] : null,
            'min_size' => is_numeric($filters['min_size'] ?? null) ? (float) $filters['min_size'] : null,
            'max_size' => is_numeric($filters['max_size'] ?? null) ? (float) $filters['max_size'] : null,
            'property_type' => trim((string) ($filters['property_type'] ?? '')),
            'road_access' => trim((string) ($filters['road_access'] ?? '')),
            'view_type' => trim((string) ($filters['view_type'] ?? '')),
            'sort' => trim((string) ($filters['sort'] ?? 'newest')),
        ];
    }

    private function applyBrowseFiltersToBuilder(object $builder, array $filters): void
    {
        $builder->where('land_listings.is_verified_listing', 'true')
            ->where('land_listings.province', 'Batangas')
            ->where('land_listings.city', 'Nasugbu');

        if ($filters['barangay'] !== '') {
            $builder->where('land_listings.barangay', $filters['barangay']);
        }

        if ($filters['min_price'] !== null) {
            $builder->where('land_listings.price >=', $filters['min_price']);
        }

        if ($filters['max_price'] !== null) {
            $builder->where('land_listings.price <=', $filters['max_price']);
        }

        if ($filters['min_size'] !== null) {
            $builder->where('land_listings.developing_area >=', $filters['min_size']);
        }

        if ($filters['max_size'] !== null) {
            $builder->where('land_listings.developing_area <=', $filters['max_size']);
        }

        if ($filters['property_type'] !== '') {
            $normalizedType = $this->normalizePropertyTypeFilter($filters['property_type']);
            if ($normalizedType !== '') {
                $builder->where('land_listings.property_type', $normalizedType);
            }
        }

        if ($filters['road_access'] !== '') {
            $normalizedRoadAccess = $this->normalizeRoadAccessFilter($filters['road_access']);
            if ($normalizedRoadAccess !== '') {
                $builder->where('land_listings.road_access_type', $normalizedRoadAccess);
            }
        }

        if ($filters['view_type'] !== '') {
            $normalizedViewType = $this->normalizeViewTypeFilter($filters['view_type']);
            if ($normalizedViewType !== '') {
                $builder->where('land_listings.view_type', $normalizedViewType);
            }
        }

    }

    private function normalizePropertyTypeFilter(string $propertyType): string
    {
        return match (strtolower(trim($propertyType))) {
            'residential', 'residential_land' => 'residential_land',
            'agricultural', 'agricultural_land', 'farm', 'agricultural/farm' => 'agricultural_land',
            'commercial', 'commercial_land' => 'commercial_land',
            'beach_lot' => 'beach_lot',
            default => '',
        };
    }

    private function normalizeRoadAccessFilter(string $roadAccess): string
    {
        return match (strtolower(trim($roadAccess))) {
            'concrete', 'cemented' => 'cemented',
            'dirt_road', 'right_of_way' => 'right_of_way',
            'highway_access', 'none' => 'none',
            default => '',
        };
    }

    private function normalizeViewTypeFilter(string $viewType): string
    {
        return match (strtolower(trim($viewType))) {
            'beach_view', 'sea_view' => 'sea_view',
            'mountain_view' => 'mountain_view',
            'plain', 'none' => 'none',
            default => '',
        };
    }

    private function isNasugbuListing(array $listing): bool
    {
        $province = strtolower(trim((string) ($listing['province'] ?? '')));
        $city = strtolower(trim((string) ($listing['city'] ?? '')));

        return $province === 'batangas' && ($city === 'nasugbu' || $city === 'nasugbo');
    }

    private function getBrowseFilterOptions(): array
    {
        $db = Database::connect();
        if (! $db->tableExists('land_listings')) {
            return [
                'locationLabel' => 'Nasugbu, Batangas',
                'barangays' => self::NASUGBU_BARANGAYS,
                'price' => ['min' => 0, 'max' => 0],
                'size' => ['min' => 0, 'max' => 0],
            ];
        }

        $rows = $db->table('land_listings')
            ->select('barangay, price, developing_area')
            ->where('is_verified_listing', 'true')
            ->where('province', 'Batangas')
            ->where('city', 'Nasugbu')
            ->orderBy('barangay', 'ASC')
            ->get()
            ->getResultArray();

        $minPrice = null;
        $maxPrice = null;
        $minSize = null;
        $maxSize = null;

        foreach ($rows as $row) {
            $price = is_numeric($row['price'] ?? null) ? (float) $row['price'] : null;
            $size = is_numeric($row['developing_area'] ?? null) ? (float) $row['developing_area'] : null;

            if ($price !== null) {
                $minPrice = $minPrice === null ? $price : min($minPrice, $price);
                $maxPrice = $maxPrice === null ? $price : max($maxPrice, $price);
            }

            if ($size !== null) {
                $minSize = $minSize === null ? $size : min($minSize, $size);
                $maxSize = $maxSize === null ? $size : max($maxSize, $size);
            }
        }

        return [
            'locationLabel' => 'Nasugbu, Batangas',
            'barangays' => self::NASUGBU_BARANGAYS,
            'price' => [
                'min' => (float) ($minPrice ?? 0),
                'max' => (float) ($maxPrice ?? 0),
            ],
            'size' => [
                'min' => (float) ($minSize ?? 0),
                'max' => (float) ($maxSize ?? 0),
            ],
        ];
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

    private function getListingImagesByListing(array $listingIds): array
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
            $imagePath = trim((string) ($row['image_path'] ?? ''));
            if ($listingId <= 0 || $imagePath === '') {
                continue;
            }

            $images[$listingId] ??= [];
            $images[$listingId][] = $imagePath;
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
            'beach_lot' => 'Beach Lot',
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
            'cemented' => 'Concrete',
            'right_of_way' => 'Dirt Road',
            'none' => 'Highway Access',
            default => 'Road Access N/A',
        };
    }

    private function formatViewType(string $viewType): string
    {
        return match ($viewType) {
            'mountain', 'mountain_view' => 'Mountain View',
            'sea', 'sea_view', 'ocean_view' => 'Beach View',
            'none' => 'Plain',
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
            ->select('i.inquiry_id, i.listing_id, i.seller_id, i.inquiry_status, i.created_at, i.updated_at, l.title, l.price, ms.session_id, su.first_name AS seller_first_name, su.last_name AS seller_last_name')
            ->join('land_listings l', 'l.listing_id = i.listing_id', 'left')
            ->join('message_sessions ms', 'ms.inquiry_id = i.inquiry_id', 'left')
            ->join('users su', 'su.user_id = i.seller_id', 'left')
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
            $sellerName = trim((string) (($row['seller_first_name'] ?? '') . ' ' . ($row['seller_last_name'] ?? '')));
            if ($sellerName === '') {
                $sellerName = 'Seller';
            }

            $inquiries[] = [
                'inquiry_id' => (int) ($row['inquiry_id'] ?? 0),
                'listing_id' => $listingId,
                'session_id' => (int) ($row['session_id'] ?? 0),
                'seller_name' => $sellerName,
                'seller_initials' => $this->formatInitials($sellerName),
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
