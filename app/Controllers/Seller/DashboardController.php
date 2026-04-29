<?php

namespace App\Controllers\Seller;

use App\Controllers\BaseController;
use App\Models\LandListings;
use App\Models\SellerVerificationModel;
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

        [$sellerListings, $listingCounts] = $this->getSellerListingsPayload($userId);
        $sellerInquiries = $this->getSellerInquiriesPayload($userId);
        $sidebarCounts = $this->getSellerSidebarCounts($userId);

        return view('Pages/Seller/Dashboard_Seller', [
            'fullname' => $Fullname,
            'userProfile' => $userProfile,
            'sellerListings' => $sellerListings,
            'listingCounts' => $listingCounts,
            'sellerInquiries' => $sellerInquiries,
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
            'counts' => $this->getSellerSidebarCounts($userId),
        ]);
    }

    public function dashboardSection(): ResponseInterface|string
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

        [$sellerListings] = $this->getSellerListingsPayload($userId);
        $sellerInquiries = $this->getSellerInquiriesPayload($userId);

        return view('Pages/Seller/Components/DashboardSection', [
            'sellerListings' => $sellerListings,
            'sellerInquiries' => $sellerInquiries,
        ]);
    }

    private function getCurrentUserProfile(int $userId): array
    {
        if ($userId <= 0) {
            return [
                'full_name' => 'Seller',
                'email' => 'N/A',
                'avatar_url' => '',
                'initials' => 'NA',
                'account_status_label' => 'Inactive Seller',
                'account_status_class' => 'inactive',
                'verification_label' => 'Not Verified',
                'verification_class' => 'pending',
            ];
        }

        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId) ?? [];
        $fullName = trim((string) (($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
        $fullName = $fullName !== '' ? $fullName : 'Seller';
        $isActive = (int) ($user['is_active'] ?? 0) === 1;

        $verificationModel = new SellerVerificationModel();
        $totalDocuments = $verificationModel->where('seller_id', $userId)->countAllResults();
        $verifiedDocuments = $verificationModel
            ->where('seller_id', $userId)
            ->where('is_verified', 1)
            ->countAllResults();

        $verificationLabel = 'Not Verified';
        $verificationClass = 'pending';

        if ($verifiedDocuments > 0) {
            $verificationLabel = 'Verified Seller';
            $verificationClass = 'verified';
        } elseif ($totalDocuments > 0) {
            $verificationLabel = 'Pending Verification';
            $verificationClass = 'pending';
        }

        return [
            'full_name' => $fullName,
            'email' => trim((string) ($user['email'] ?? 'N/A')),
            'avatar_url' => $this->resolveUserProfilePictureUrl((string) ($user['profile_picture'] ?? '')),
            'initials' => $this->formatInitials($fullName),
            'account_status_label' => $isActive ? 'Active Seller' : 'Inactive Seller',
            'account_status_class' => $isActive ? 'active' : 'inactive',
            'verification_label' => $verificationLabel,
            'verification_class' => $verificationClass,
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

    private function getSellerSidebarCounts(int $sellerId): array
    {
        $db = Database::connect();

        $listingsTotal = $db->table('land_listings')
            ->where('seller_id', $sellerId)
            ->countAllResults();

        $acceptedInquiries = $db->table('inquiries')
            ->where('seller_id', $sellerId)
            ->where('inquiry_status', 'accepted')
            ->countAllResults();

        $unreadMessages = $db->table('messages m')
            ->select('COUNT(*) AS total_unread')
            ->join('message_sessions ms', 'ms.session_id = m.session_id', 'inner')
            ->groupStart()
            ->where('ms.seller_id', $sellerId)
            ->orWhere('ms.buyer_id', $sellerId)
            ->groupEnd()
            ->where('m.sender_id !=', $sellerId)
            ->where('m.is_read', 0)
            ->get()
            ->getRowArray();

        return [
            'listings_total' => (int) $listingsTotal,
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

    private function getSellerListingsPayload(int $sellerId): array
    {
        $listingModel = new LandListings();
        $listings = $listingModel
            ->where('seller_id', $sellerId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $counts = [
            'all' => count($listings),
            'available' => 0,
            'in_inquiry' => 0,
            'reserved' => 0,
            'closed' => 0,
            'pending' => 0,
            'rejected' => 0,
        ];

        if ($listings === []) {
            return [[], $counts];
        }

        $listingIds = array_map(static fn(array $listing): int => (int) ($listing['listing_id'] ?? 0), $listings);
        $listingIds = array_values(array_filter($listingIds));

        $primaryImages = $this->getPrimaryImagesByListing($listingIds);
        $viewCounts = $this->getViewCountsByListing($listingIds);
        $formattedListings = [];

        foreach ($listings as $listing) {
            $status = (string) ($listing['listing_status'] ?? '');
            if (array_key_exists($status, $counts)) {
                $counts[$status]++;
            }

            $formattedListings[] = $this->formatListingForDashboard($listing, $primaryImages, $viewCounts);
        }

        return [$formattedListings, $counts];
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

    private function getViewCountsByListing(array $listingIds): array
    {
        if ($listingIds === []) {
            return [];
        }

        $db = Database::connect();
        if (! $db->tableExists('listing_analytics')) {
            return [];
        }

        $rows = $db->table('listing_analytics')
            ->select('listing_id, total_views')
            ->whereIn('listing_id', $listingIds)
            ->get()
            ->getResultArray();

        $viewCounts = [];

        foreach ($rows as $row) {
            $listingId = (int) ($row['listing_id'] ?? 0);
            if ($listingId <= 0) {
                continue;
            }

            $viewCounts[$listingId] = (int) ($row['total_views'] ?? 0);
        }

        return $viewCounts;
    }

    private function formatListingForDashboard(array $listing, array $primaryImages, array $viewCounts): array
    {
        $listingId = (int) ($listing['listing_id'] ?? 0);
        $status = (string) ($listing['listing_status'] ?? '');
        $statusMeta = $this->getListingStatusMeta($status);
        $verificationMeta = $this->getListingVerificationMeta($listing);

        return [
            'listing_id' => $listingId,
            'title' => trim((string) ($listing['title'] ?? 'Untitled Listing')),
            'location_label' => $this->formatLocation($listing),
            'property_type_label' => $this->formatPropertyType((string) ($listing['property_type'] ?? '')),
            'document_status_label' => $this->formatDocumentStatus($listing),
            'verification_label' => $verificationMeta['label'],
            'verification_class' => $verificationMeta['class'],
            'verification_note' => $verificationMeta['note'],
            'status_key' => $statusMeta['key'],
            'status_label' => $statusMeta['label'],
            'status_class' => $statusMeta['class'],
            'price_value' => (float) ($listing['price'] ?? 0),
            'view_count' => (int) ($viewCounts[$listingId] ?? 0),
            'image_url' => $this->resolveListingImageUrl($primaryImages[$listingId] ?? null, (string) ($listing['title'] ?? 'Landly Listing')),
        ];
    }

    private function getListingStatusMeta(string $status): array
    {
        return match ($status) {
            'available' => ['key' => 'available', 'label' => 'Available', 'class' => 'available'],
            'in_inquiry' => ['key' => 'in_inquiry', 'label' => 'In Inquiry', 'class' => 'inquiry'],
            'reserved' => ['key' => 'reserved', 'label' => 'Reserved', 'class' => 'reserved'],
            'closed' => ['key' => 'closed', 'label' => 'Closed', 'class' => 'closed'],
            'pending' => ['key' => 'pending', 'label' => 'Pending Review', 'class' => 'pending'],
            'rejected' => ['key' => 'rejected', 'label' => 'Rejected', 'class' => 'rejected'],
            'suspended' => ['key' => 'suspended', 'label' => 'Suspended', 'class' => 'suspended'],
            'sold' => ['key' => 'sold', 'label' => 'Sold', 'class' => 'sold'],
            default => ['key' => 'other', 'label' => 'Unknown', 'class' => 'pending'],
        };
    }

    private function getListingVerificationMeta(array $listing): array
    {
        $verification = strtolower(trim((string) ($listing['is_verified_listing'] ?? 'pending')));

        return match ($verification) {
            'true', '1' => [
                'label' => 'Verified',
                'class' => 'verified',
                'note' => 'Visible to buyers',
            ],
            'rejected' => [
                'label' => 'Rejected',
                'class' => 'rejected',
                'note' => 'Hidden from buyers until updated and approved',
            ],
            'false', '0' => [
                'label' => 'Not Verified',
                'class' => 'pending',
                'note' => 'Not yet approved for buyer visibility',
            ],
            default => [
                'label' => 'Pending Verification',
                'class' => 'pending',
                'note' => 'Seller-only while waiting for approval',
            ],
        };
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

    private function formatLocation(array $listing): string
    {
        $parts = array_filter([
            trim((string) ($listing['barangay'] ?? '')),
            trim((string) ($listing['city'] ?? '')),
            trim((string) ($listing['province'] ?? '')),
        ]);

        return $parts !== [] ? implode(', ', $parts) : 'Location unavailable';
    }

    private function resolveListingImageUrl(?string $imagePath, string $title): string
    {
        $imagePath = trim((string) $imagePath);
        $fallbackUrl = base_url('default1.png');

        if ($imagePath !== '') {
            if (preg_match('#^(?:https?:)?//#i', $imagePath) === 1 || str_starts_with($imagePath, 'data:')) {
                return $imagePath;
            }

            $normalizedPath = ltrim(str_replace('\\', '/', $imagePath), '/');
            return is_file(FCPATH . $normalizedPath) ? base_url($normalizedPath) : $fallbackUrl;
        }

        return $fallbackUrl;
    }

    private function getSellerInquiriesPayload(int $sellerId): array
    {
        $db = Database::connect();

        $rows = $db->table('inquiries i')
            ->select('i.inquiry_id, i.listing_id, i.buyer_id, i.inquiry_status, i.created_at, i.updated_at, l.title, l.price, ms.session_id, u.first_name, u.last_name')
            ->join('land_listings l', 'l.listing_id = i.listing_id', 'left')
            ->join('message_sessions ms', 'ms.inquiry_id = i.inquiry_id', 'left')
            ->join('users u', 'u.user_id = i.buyer_id', 'left')
            ->where('i.seller_id', $sellerId)
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
            $buyerName = trim((string) (($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')));
            $buyerName = $buyerName !== '' ? $buyerName : 'Buyer';
            $status = strtolower(trim((string) ($row['inquiry_status'] ?? 'pending')));

            $inquiries[] = [
                'inquiry_id' => (int) ($row['inquiry_id'] ?? 0),
                'session_id' => (int) ($row['session_id'] ?? 0),
                'listing_id' => $listingId,
                'created_at' => (string) ($row['created_at'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
                'buyer_id' => (int) ($row['buyer_id'] ?? 0),
                'buyer_name' => $buyerName,
                'buyer_initials' => $this->formatInitials($buyerName),
                'status_label' => $this->formatSellerInquiryStatusLabel($status),
                'status_class' => $this->formatSellerInquiryStatusClass($status),
                'title' => $title,
                'price_label' => '₱' . number_format((float) ($row['price'] ?? 0), 2),
                'image_url' => $this->resolveListingImageUrl($primaryImages[$listingId] ?? null, $title),
                'date_label' => $this->formatSellerInquiryDate((string) ($row['created_at'] ?? '')),
                'message_preview' => $this->formatSellerInquiryPreview($status),
            ];
        }

        return $inquiries;
    }

    private function formatSellerInquiryStatusLabel(string $status): string
    {
        return match ($status) {
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
            'reserved' => 'Reserved',
            'closed' => 'Closed',
            default => 'Pending',
        };
    }

    private function formatSellerInquiryStatusClass(string $status): string
    {
        return match ($status) {
            'accepted' => 'accepted',
            'rejected' => 'rejected',
            'reserved' => 'reserved',
            'closed' => 'closed',
            default => 'pending',
        };
    }

    private function formatSellerInquiryDate(string $rawDate): string
    {
        if ($rawDate === '') {
            return 'Date unavailable';
        }

        $timestamp = strtotime($rawDate);
        if ($timestamp === false) {
            return 'Date unavailable';
        }

        return date('M d, Y g:i A', $timestamp);
    }

    private function formatSellerInquiryPreview(string $status): string
    {
        return match ($status) {
            'accepted' => 'You accepted this buyer inquiry.',
            'rejected' => 'You rejected this buyer inquiry.',
            'reserved' => 'This inquiry is now marked reserved.',
            'closed' => 'This inquiry thread has been closed.',
            default => 'New inquiry received. Open conversation to reply.',
        };
    }

    private function formatInitials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $parts = array_values(array_filter($parts));

        if ($parts === []) {
            return 'NA';
        }

        $first = mb_substr((string) ($parts[0] ?? ''), 0, 1);
        $second = mb_substr((string) ($parts[1] ?? ''), 0, 1);

        return strtoupper($first . $second);
    }
}
