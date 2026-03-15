<?php

namespace App\Controllers\Buyer;

use App\Controllers\BaseController;
use App\Models\LandListings;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;


class DashboardController extends BaseController
{
    public function index(): ResponseInterface|string
    {
        $userId = $this->getCurrentUserId();
        $Fullname = $this->getCurrentUserFullName();

        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ]);
        }

        [$browseListings, $browsePropertyData] = $this->getBrowseListingsPayload();
        $buyerInquiries = $this->getBuyerInquiriesPayload($userId);

        return view('Pages/Buyer/Dashboard_Buyer', [
            'fullname' => $Fullname,
            'browseListings' => $browseListings,
            'browsePropertyData' => $browsePropertyData,
            'buyerInquiries' => $buyerInquiries,
        ]);
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
            ->select('land_listings.*, users.first_name, users.last_name, users.email, users.created_at AS seller_created_at')
            ->join('users', 'users.user_id = land_listings.seller_id', 'left')
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
            $sellerName = $this->formatSellerName($row);
            $sellerInitials = $this->formatInitials($sellerName);

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
                'price' => $this->formatPeso((float) ($row['price'] ?? 0)),
                'pricePerSqm' => 'N/A',
                'area' => 'N/A',
                'type' => $propertyTypeLabel,
                'titleStatus' => $documentStatusLabel,
                'location' => $locationLabel,
                'coordinates' => ['lat' => 14.5995, 'lng' => 120.9842],
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
