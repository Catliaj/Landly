<?php

namespace App\Controllers\Seller;

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

        [$sellerListings, $listingCounts] = $this->getSellerListingsPayload($userId);

        return view('Pages/Seller/Dashboard_Seller', [
            'fullname' => $Fullname,
            'sellerListings' => $sellerListings,
            'listingCounts' => $listingCounts,
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
}
