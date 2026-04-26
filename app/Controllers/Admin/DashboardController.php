<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\LandListings;
use App\Models\ReportsModel;
use App\Models\SellerVerificationModel;

class DashboardController extends BaseController
{
    protected $userModel;
    protected $listingModel;
    protected $reportsModel;
    protected $sellerVerificationModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->listingModel = new LandListings();
        $this->reportsModel = new ReportsModel();
        $this->sellerVerificationModel = new SellerVerificationModel();
    }

    public function index()
    {
        // Fetch sellers who are pending approval (have uploaded documents but not verified)
        // Get sellers with at least one unverified document or inactive status
        $db = \Config\Database::connect();
        
        $sellers = $db->query("
            SELECT DISTINCT u.*, COUNT(sv.document_id) as doc_count
            FROM users u
            LEFT JOIN seller_verification_documents sv ON u.user_id = sv.seller_id
            WHERE u.roles = 'seller' 
            GROUP BY u.user_id
            ORDER BY u.created_at DESC
        ")->getResult('array');
        
        // Enrich sellers with their documents
        $sellersData = [];
        foreach ($sellers as $seller) {
            $documents = $this->sellerVerificationModel
                ->where('seller_id', $seller['user_id'])
                ->findAll();
            
            // Create fullname from first_name and last_name
            $seller['fullname'] = trim(($seller['first_name'] ?? '') . ' ' . ($seller['last_name'] ?? ''));
            $seller['documents'] = $documents;
            $sellersData[] = $seller;
        }
        
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
            'reportStats' => [
                'pending' => $this->reportsModel->where('status', 'pending')->countAllResults(),
                'resolved' => $this->reportsModel->where('status', 'resolved')->countAllResults(),
                'suspended' => $this->reportsModel->where('status', 'suspended')->countAllResults(),
            ],
            'verificationStats' => [
                'verified' => $this->sellerVerificationModel->countAllResults(),
                'pending' => 0,
                'unverified' => 0,
            ],
            'users' => $this->userModel->findAll(),
            'listings' => $this->listingModel->findAll(),
            'sellers' => $sellersData,
            'reports' => $this->reportsModel->findAll(),
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

    public function approveListing($listingId)
    {
        if ($this->request->isAJAX()) {
            if (!$listingId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Invalid listing ID']);
            }

            // Update listing as approved/verified
            $this->listingModel->update($listingId, [
                'is_verified_listing' => 'verified',
                'listing_status' => 'approved'
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Listing approved successfully'
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
    }

    public function rejectListing($listingId)
    {
        if ($this->request->isAJAX()) {
            if (!$listingId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Invalid listing ID']);
            }

            $reason = $this->request->getJSON()->reason ?? null;

            // Update listing as rejected
            $updateData = ['listing_status' => 'rejected'];
            
            // Optionally store rejection reason if your database supports it
            if ($reason) {
                $updateData['rejection_reason'] = $reason;
            }

            $this->listingModel->update($listingId, $updateData);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Listing rejected successfully'
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
    }

    public function deleteListing($listingId)
    {
        $this->listingModel->delete($listingId);
        return redirect()->to('/admin/dashboard')->with('success', 'Listing deleted successfully.');
    }

    public function approveSeller()
    {
        if ($this->request->isAJAX()) {
            $sellerId = $this->request->getJSON()->seller_id;

            if (!$sellerId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Invalid seller ID']);
            }

            // Update user as active/approved
            $this->userModel->update($sellerId, ['is_active' => 1]);

            // Mark seller documents as verified
            $this->sellerVerificationModel
                ->where('seller_id', $sellerId)
                ->set(['is_verified' => 1])
                ->update();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Seller approved successfully'
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
    }

    public function rejectSeller()
    {
        if ($this->request->isAJAX()) {
            $sellerId = $this->request->getJSON()->seller_id;

            if (!$sellerId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Invalid seller ID']);
            }

            // Deactivate seller
            $this->userModel->update($sellerId, ['is_active' => 0]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Seller rejected successfully'
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
    }

    public function viewListing($listingId)
    {
        $listing = $this->listingModel->find($listingId);
        if (! $listing) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Listing not found'
            ])->setStatusCode(404);
        }

        $listingImages = model('ListingImages')->where('listing_id', $listingId)->findAll();
        $listingDocuments = model('ListingDocuments')->where('listing_id', $listingId)->findAll();
        $seller = $this->userModel->find($listing['seller_id']);

        // Return JSON for AJAX requests
        return $this->response->setJSON([
            'success' => true,
            'listing' => $listing,
            'images' => $listingImages,
            'documents' => $listingDocuments,
            'seller' => $seller,
        ]);
    }

    public function getSellerDocument($documentId)
    {
        // Log the attempt
        log_message('info', 'Admin accessing document: ' . $documentId . ' with role: ' . session()->get('roles'));
        
        // Verify user is logged in
        if (!session()->has('user_id')) {
            log_message('warning', 'Unauthorized document access - no session');
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Not authenticated']);
        }

        // Verify user is admin
        $userRole = session()->get('roles');
        if (strpos($userRole, 'admin') === false) {
            log_message('warning', 'Unauthorized document access - role: ' . $userRole);
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
        }

        // Fetch document
        $document = $this->sellerVerificationModel->find($documentId);
        if (!$document) {
            log_message('warning', 'Document not found: ' . $documentId);
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Document not found']);
        }

        $filePath = WRITEPATH . 'uploads/' . $document['file_path'];
        
        if (!file_exists($filePath)) {
            log_message('warning', 'File not found: ' . $filePath);
            return $this->response->setStatusCode(404)->setJSON(['error' => 'File not found']);
        }

        log_message('info', 'Serving document: ' . $filePath);
        
        // Serve the file inline (display in browser) instead of downloading
        return $this->response
            ->download($filePath, null, true);
    }
}
