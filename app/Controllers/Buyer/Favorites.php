<?php

namespace App\Controllers\Buyer;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

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
            ->select('buyer_favorites.*, land_listings.title, land_listings.price, land_listings.property_type')
            ->join('land_listings', 'land_listings.listing_id = buyer_favorites.listing_id', 'left')
            ->where('buyer_favorites.buyer_id', $buyerId)
            ->orderBy('buyer_favorites.created_at', 'DESC')
            ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'count' => count($favorites),
            'favorites' => $favorites
        ]);
    }
}
