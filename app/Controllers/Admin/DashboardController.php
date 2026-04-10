<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\LandListings;
use App\Models\ReportsModel;

class DashboardController extends BaseController
{
    protected $userModel;
    protected $listingModel;
    protected $reportsModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->listingModel = new LandListings();
        $this->reportsModel = new ReportsModel();
    }

    public function index()
    {
        $data = [
            'fullname' => session()->get('fullname') ?? 'Admin',
            'totalUsers' => $this->userModel->countAllResults(),
            'totalBuyers' => $this->userModel->where('roles', 'buyer')->countAllResults(),
            'totalSellers' => $this->userModel->where('roles', 'seller')->countAllResults(),
            'totalAdmins' => $this->userModel->where('roles', 'admin')->countAllResults(),
            'totalListings' => $this->listingModel->countAllResults(),
            'listingStats' => [
                'pending' => $this->listingModel->where('listing_status', 'pending')->countAllResults(),
                'verified' => $this->listingModel->where('is_verified_listing', 'verified')->countAllResults(),
                'rejected' => $this->listingModel->where('listing_status', 'rejected')->countAllResults(),
            ],
            'totalReports' => $this->reportsModel->where('status', 'pending')->countAllResults(),
            'users' => $this->userModel->findAll(),
            'listings' => $this->listingModel->findAll(),
        ];

        return view('Pages/Admin/Dashboard_Admin', $data);
    }

    public function activateUser($userId)
    {
        $this->userModel->update($userId, ['is_active' => 1]);
        return redirect()->to('/admin/dashboard')->with('success', 'User activated successfully.');
    }

    public function deactivateUser($userId)
    {
        $this->userModel->update($userId, ['is_active' => 0]);
        return redirect()->to('/admin/dashboard')->with('success', 'User deactivated successfully.');
    }

    public function deleteUser($userId)
    {
        $this->userModel->delete($userId);
        return redirect()->to('/admin/dashboard')->with('success', 'User deleted successfully.');
    }

    public function verifyListing($listingId)
    {
        $this->listingModel->update($listingId, ['is_verified_listing' => 'verified', 'listing_status' => 'approved']);
        return redirect()->to('/admin/dashboard')->with('success', 'Listing verified successfully.');
    }

    public function rejectListing($listingId)
    {
        $this->listingModel->update($listingId, ['listing_status' => 'rejected']);
        return redirect()->to('/admin/dashboard')->with('success', 'Listing rejected.');
    }

    public function deleteListing($listingId)
    {
        $this->listingModel->delete($listingId);
        return redirect()->to('/admin/dashboard')->with('success', 'Listing deleted successfully.');
    }

    public function viewListing($listingId)
    {
        $listing = $this->listingModel->find($listingId);
        if (! $listing) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Listing not found.");
        }

        $listingImages = model('ListingImages')->where('listing_id', $listingId)->findAll();
        $listingDocuments = model('ListingDocuments')->where('listing_id', $listingId)->findAll();
        $seller = $this->userModel->find($listing['seller_id']);

        $data = [
            'fullname' => session()->get('fullname') ?? 'Admin',
            'listing' => $listing,
            'images' => $listingImages,
            'documents' => $listingDocuments,
            'seller' => $seller,
        ];

        return view('Pages/Admin/Listing_Detail', $data);
    }
}
