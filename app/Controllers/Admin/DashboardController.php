<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\LandListings;
use App\Models\ReportsModel;
use App\Models\SellerVerificationModel;
use App\Models\ListingImages;

class DashboardController extends BaseController
{
    protected $userModel;
    protected $listingModel;
    protected $reportsModel;
    protected $sellerVerificationModel;
    protected $listingImagesModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->listingModel = new LandListings();
        $this->reportsModel = new ReportsModel();
        $this->sellerVerificationModel = new SellerVerificationModel();
        $this->listingImagesModel = new ListingImages();
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
        
        // Enrich sellers with their documents and resolve profile pictures
        $sellersData = [];
        foreach ($sellers as $seller) {
            $documents = $this->sellerVerificationModel
                ->where('seller_id', $seller['user_id'])
                ->findAll();
            
            // Create fullname from first_name and last_name
            $seller['fullname'] = trim(($seller['first_name'] ?? '') . ' ' . ($seller['last_name'] ?? ''));
            $seller['documents'] = $documents;
            
            // Resolve profile picture URL
            $profilePicture = trim((string) ($seller['profile_picture'] ?? ''));
            if ($profilePicture !== '') {
                if (preg_match('#^(?:https?:)?//#i', $profilePicture) === 1 || str_starts_with($profilePicture, 'data:')) {
                    $seller['profile_picture_url'] = $profilePicture;
                } else {
                    $seller['profile_picture_url'] = base_url('media/profile?path=' . rawurlencode($profilePicture));
                }
            } else {
                $seller['profile_picture_url'] = null;
            }
            
            $sellersData[] = $seller;
        }
        
        // Fetch all listings with image data
        $listings = $this->listingModel->findAll();
        $listingsData = [];
        foreach ($listings as $listing) {
            $listingId = $listing['listing_id'];
            
            // Get primary image for this listing
            $primaryImage = $db->table('listing_images')
                ->where('listing_id', $listingId)
                ->where('is_primary', 1)
                ->get()
                ->getRow('array');
            
            if ($primaryImage && !empty($primaryImage['image_path'])) {
                $imagePath = trim($primaryImage['image_path']);
                if (preg_match('#^(?:https?:)?//#i', $imagePath) === 1 || str_starts_with($imagePath, 'data:')) {
                    $listing['image_url'] = $imagePath;
                } else {
                    $listing['image_url'] = base_url(ltrim(str_replace('\\', '/', $imagePath), '/'));
                }
            } else {
                // Fall back to SVG placeholder with listing title
                $title = trim($listing['title'] ?? '') ?: 'Land Listing';
                $svg = sprintf(
                    '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="480" viewBox="0 0 800 480"><rect width="800" height="480" fill="#183127"/><rect x="36" y="36" width="728" height="408" rx="30" fill="#234236"/><text x="50%%" y="50%%" text-anchor="middle" dominant-baseline="middle" fill="#d2b48c" font-family="Arial, sans-serif" font-size="34">%s</text></svg>',
                    htmlspecialchars($title, ENT_QUOTES, 'UTF-8')
                );
                $listing['image_url'] = 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
            }
            
            $listingsData[] = $listing;
        }
        
        // Enrich users with verification status and report counts
        $usersData = [];
        $allUsers = $this->userModel->findAll();
        foreach ($allUsers as $user) {
            $userId = $user['user_id'];
            
            // Get seller verification status (only applies to sellers)
            $verificationStatus = 'unverified';
            if ($user['roles'] === 'seller') {
                $verifiedDocs = $this->sellerVerificationModel
                    ->where('seller_id', $userId)
                    ->where('is_verified', 1)
                    ->countAllResults();
                
                $pendingDocs = $this->sellerVerificationModel
                    ->where('seller_id', $userId)
                    ->where('is_verified', 0)
                    ->where('reviewed_at', null)
                    ->countAllResults();
                
                if ($verifiedDocs > 0) {
                    $verificationStatus = 'verified';
                } elseif ($pendingDocs > 0) {
                    $verificationStatus = 'pending';
                }
            }
            
            // Get report counts
            $reportsFiled = $this->reportsModel
                ->where('reporter_user_id', $userId)
                ->countAllResults();
            
            $reportsAgainst = $this->reportsModel
                ->where('reported_user_id', $userId)
                ->countAllResults();
            
            $user['verification_status'] = $verificationStatus;
            $user['reports_filed'] = $reportsFiled;
            $user['reports_against'] = $reportsAgainst;
            
            $usersData[] = $user;
        }
        
        // Enrich reports with user names
        $reportsData = [];
        $allReports = $this->reportsModel->findAll();
        $userMap = [];
        foreach ($allUsers as $u) {
            $userMap[$u['user_id']] = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
        }
        
        foreach ($allReports as $report) {
            $reportedById = $report['reporter_user_id'] ?? null;
            $reportedAgainstId = $report['reported_user_id'] ?? null;
            
            $report['reported_by_name'] = !empty($reportedById) && isset($userMap[$reportedById]) 
                ? $userMap[$reportedById] 
                : 'Unknown User';
            
            $report['reported_against_name'] = !empty($reportedAgainstId) && isset($userMap[$reportedAgainstId])
                ? $userMap[$reportedAgainstId]
                : 'Unknown User';

            $report['subject'] = $this->formatReportSubject($report);
            
            $reportsData[] = $report;
        }
        
        $data = [
            'fullname' => session()->get('fullname') ?? 'Admin',
            'totalUsers' => $this->userModel->countAllResults(),
            'totalBuyers' => $this->userModel->where('roles', 'buyer')->countAllResults(),
            'totalSellers' => $this->userModel->where('roles', 'seller')->countAllResults(),
            'totalAdmins' => $this->userModel->where('roles', 'admin')->countAllResults(),
            'totalListings' => $this->listingModel->countAllResults(),
            'listingStats' => [
                'pending' => $this->listingModel->where('is_verified_listing', 'pending')->countAllResults(),
                'verified' => $this->listingModel->where('is_verified_listing', 'true')->countAllResults(),
                'rejected' => $this->listingModel->where('is_verified_listing', 'rejected')->countAllResults(),
            ],
            'totalReports' => $this->reportsModel->where('status', 'pending')->countAllResults(),
            'reportStats' => [
                'pending' => $this->reportsModel->where('status', 'pending')->countAllResults(),
                'resolved' => $this->reportsModel->whereIn('status', ['reviewed', 'action_taken'])->countAllResults(),
                'suspended' => $this->reportsModel->where('status', 'dismissed')->countAllResults(),
            ],
            'verificationStats' => [
                'verified' => $this->sellerVerificationModel->where('is_verified', 1)->countAllResults(),
                'pending' => $this->sellerVerificationModel->where('is_verified', 0)->where('reviewed_at', null)->countAllResults(),
                'unverified' => $this->sellerVerificationModel->where('is_verified', null)->countAllResults(),
            ],
            'users' => $usersData,
            'listings' => $listingsData,
            'sellers' => $sellersData,
            'reports' => $reportsData,
        ];

        return view('Pages/Admin/Dashboard_Admin', $data);
    }

    private function formatReportSubject(array $report): string
    {
        $type = ucfirst((string) ($report['report_type'] ?? 'Report'));
        $reason = trim((string) ($report['reason'] ?? ''));
        $otherReason = trim((string) ($report['other_reason'] ?? ''));

        if ($reason === 'Other' && $otherReason !== '') {
            $reason = $otherReason;
        }

        return trim($type . ($reason !== '' ? ': ' . $reason : ' Report'));
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

            try {
                // Update listing as approved/verified
                // is_verified_listing: set to 'true' (validated value, not 'verified' which fails validation)
                // listing_status: set to 'approved' to indicate admin approval
                $updateResult = $this->listingModel->update($listingId, [
                    'is_verified_listing' => 'true',
                    'listing_status' => 'approved'
                ]);

                // Check if update was successful
                if ($updateResult === false) {
                    $errors = $this->listingModel->errors();
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to approve listing: ' . implode(', ', $errors)
                    ]);
                }

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Listing approved successfully'
                ]);
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error approving listing: ' . $e->getMessage()
                ]);
            }
        }

        return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
    }

    public function rejectListing($listingId)
    {
        if ($this->request->isAJAX()) {
            if (!$listingId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Invalid listing ID']);
            }

            try {
                $reason = $this->request->getJSON()->reason ?? null;

                // Update listing as rejected
                // is_verified_listing: set to 'rejected' (validated value)
                // listing_status: set to 'rejected' to indicate admin rejection
                $updateData = [
                    'is_verified_listing' => 'rejected',
                    'listing_status' => 'rejected'
                ];
                
                // Optionally store rejection reason if database supports it
                if ($reason) {
                    $updateData['rejection_reason'] = $reason;
                }

                $updateResult = $this->listingModel->update($listingId, $updateData);

                // Check if update was successful
                if ($updateResult === false) {
                    $errors = $this->listingModel->errors();
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to reject listing: ' . implode(', ', $errors)
                    ]);
                }

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Listing rejected successfully'
                ]);
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error rejecting listing: ' . $e->getMessage()
                ]);
            }
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
                'message' => 'Listing not found.',
            ], 404);
        }

        $seller = $this->userModel->find($listing['seller_id']);

        return $this->response->setJSON([
            'success' => true,
            'listing' => $listing,
            'seller' => $seller ? $seller['fullname'] ?? 'Unknown' : 'Unknown',
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
