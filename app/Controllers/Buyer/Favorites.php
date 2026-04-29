<?php

namespace App\Controllers\Buyer;

use App\Controllers\BaseController;
use Config\Database;

class Favorites extends BaseController
{
    /**
     * Display all favorites for the current buyer
     */
    public function index()
    {
        $buyerId = $this->getCurrentUserId();
        
        if (!$buyerId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $favoriteModel = model('BuyerFavoriteModel');
        $favorites = $favoriteModel
            ->where('buyer_id', $buyerId)
            ->findAll();

        return $this->response->setJSON($favorites);
    }

    /**
     * Toggle a listing as favorite (add if not exists, remove if exists)
     */
    public function toggle()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $buyerId = $this->getCurrentUserId();
        if (!$buyerId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $listingId = $this->request->getPost('listing_id');
        if (!$listingId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Listing ID is required']);
        }

        // Check if listing exists
        $listingModel = model('LandListings');
        $listing = $listingModel->find($listingId);
        if (!$listing) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Listing not found']);
        }

        $favoriteModel = model('BuyerFavoriteModel');
        
        // Check if already in favorites
        $existingFavorite = $favoriteModel
            ->where('buyer_id', $buyerId)
            ->where('listing_id', $listingId)
            ->first();

        if ($existingFavorite) {
            // Remove from favorites
            $favoriteModel->delete($existingFavorite['favorite_id']);
            return $this->response->setJSON([
                'success' => true,
                'action' => 'removed',
                'message' => 'Listing removed from favorites'
            ]);
        } else {
            // Add to favorites
            $favoriteModel->insert([
                'buyer_id' => $buyerId,
                'listing_id' => $listingId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            return $this->response->setJSON([
                'success' => true,
                'action' => 'added',
                'message' => 'Listing added to favorites'
            ]);
        }
    }

    /**
     * Check if a specific listing is favorited by the current buyer
     */
    public function isFavorited()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $buyerId = $this->getCurrentUserId();
        if (!$buyerId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $listingId = $this->request->getPost('listing_id');
        if (!$listingId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Listing ID is required']);
        }

        $favoriteModel = model('BuyerFavoriteModel');
        $isFavorited = $favoriteModel
            ->where('buyer_id', $buyerId)
            ->where('listing_id', $listingId)
            ->countAllResults() > 0;

        return $this->response->setJSON([
            'isFavorited' => $isFavorited,
            'listing_id' => $listingId
        ]);
    }

    /**
     * Get all favorites for the current buyer with listing details
     */
    public function getBuyerFavorites()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $buyerId = $this->getCurrentUserId();
        if (!$buyerId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $favoriteModel = model('BuyerFavoriteModel');
        $favorites = $favoriteModel
            ->select('buyer_favorites.favorite_id, buyer_favorites.listing_id, buyer_favorites.created_at AS favorited_at, land_listings.title, land_listings.price, land_listings.property_type, land_listings.listing_status, land_listings.document_status, land_listings.is_titled, land_listings.has_tax_declaration, land_listings.road_access_type, land_listings.barangay, land_listings.city, land_listings.province, land_listings.seller_id, users.first_name, users.last_name, listing_locations.latitude AS listing_latitude, listing_locations.longitude AS listing_longitude')
            ->join('land_listings', 'land_listings.listing_id = buyer_favorites.listing_id', 'left')
            ->join('users', 'users.user_id = land_listings.seller_id', 'left')
            ->join('listing_locations', 'listing_locations.listing_id = land_listings.listing_id', 'left')
            ->where('buyer_favorites.buyer_id', $buyerId)
            ->where('land_listings.listing_status !=', 'sold')
            ->orderBy('buyer_favorites.created_at', 'DESC')
            ->findAll();

        $listingIds = array_values(array_filter(array_map(
            static fn(array $row): int => (int) ($row['listing_id'] ?? 0),
            $favorites
        )));

        $primaryImages = $this->getPrimaryImagesByListing($listingIds);

        foreach ($favorites as &$favorite) {
            $listingId = (int) ($favorite['listing_id'] ?? 0);
            $title = trim((string) ($favorite['title'] ?? 'Land Listing'));
            $favorite['property_type_label'] = $this->formatPropertyType((string) ($favorite['property_type'] ?? ''));
            $favorite['listing_status_label'] = $this->getListingStatusMeta((string) ($favorite['listing_status'] ?? ''))['label'];
            $favorite['document_status_label'] = $this->formatDocumentStatus($favorite);
            $favorite['location_label'] = $this->formatLocation($favorite);
            $favorite['road_access_label'] = $this->formatRoadAccess((string) ($favorite['road_access_type'] ?? ''));
            $favorite['seller_name'] = $this->formatSellerName($favorite);
            $favorite['seller_initials'] = $this->formatInitials($favorite['seller_name']);
            $favorite['price_label'] = '₱' . number_format((float) ($favorite['price'] ?? 0), 2);
            $favorite['image_url'] = $this->resolveListingImageUrl($primaryImages[$listingId] ?? null, $title);
            $favorite['property_type_key'] = strtolower(trim((string) ($favorite['property_type'] ?? '')));
        }
        unset($favorite);

        return $this->response->setJSON([
            'success' => true,
            'count' => count($favorites),
            'favorites' => $favorites
        ]);
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
}
