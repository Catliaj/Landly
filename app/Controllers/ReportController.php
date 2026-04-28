<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ReportsModel;
use App\Models\LandListings;
use App\Models\Messages;
use App\Models\MessageSessions;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class ReportController extends BaseController
{
    protected $reportModel;
    protected $listingModel;
    protected $messageModel;
    protected $sessionModel;
    protected $userModel;

    public function __construct()
    {
        $this->reportModel = new ReportsModel();
        $this->listingModel = new LandListings();
        $this->messageModel = new Messages();
        $this->sessionModel = new MessageSessions();
        $this->userModel = new UserModel();
    }

    /**
     * Submit a listing report
     */
    public function submitListingReport(): ResponseInterface
    {
        // Check if user is logged in
        $userId = $this->getCurrentUserId();
        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'You must be logged in to report a listing.'
            ]);
        }

        // Get request data
        $listingId = (int) $this->request->getPost('listing_id');
        $reason = $this->request->getPost('reason');
        $otherReason = $this->request->getPost('other_reason');
        $description = $this->request->getPost('description');

        // Validate listing exists
        $listing = $this->listingModel->find($listingId);
        if (!$listing) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Listing not found.'
            ]);
        }

        // Prevent reporting own listings (for sellers)
        if ($listing['seller_id'] == $userId) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'You cannot report your own listing.'
            ]);
        }

        // Validate reason
        $validReasons = ReportsModel::getListingReasons();
        if (!in_array($reason, $validReasons)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Invalid reason selected.'
            ]);
        }

        // If reason is "Other", require other_reason
        if ($reason === 'Other' && empty($otherReason)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Please describe the issue when selecting "Other".'
            ]);
        }

        // Check for duplicate pending reports
        if ($this->reportModel->hasDuplicateReport($userId, 'listing', $listingId)) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 'error',
                'message' => 'You have already reported this listing. Thank you for your report.'
            ]);
        }

        // Validate and handle evidence upload
        $evidencePath = null;
        $evidenceFile = $this->request->getFile('evidence');
        if ($evidenceFile && $evidenceFile->isValid()) {
            // Validate file
            if ($evidenceFile->getSize() > 5242880) { // 5MB
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'Evidence file must be less than 5MB.'
                ]);
            }

            $validMimes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            if (!in_array($evidenceFile->getMimeType(), $validMimes)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'Evidence must be an image (JPG, PNG, GIF) or PDF.'
                ]);
            }

            // Store the file
            $newName = $evidenceFile->getRandomName();
            $evidenceFile->move(WRITEPATH . 'uploads/reports', $newName);
            $evidencePath = 'uploads/reports/' . $newName;
        }

        // Sanitize description
        $description = empty($description) ? null : substr($description, 0, 1000);

        // Create report
        $reportData = [
            'report_type'      => 'listing',
            'reporter_user_id' => $userId,
            'reported_user_id' => $listing['seller_id'],
            'listing_id'       => $listingId,
            'reason'           => $reason,
            'other_reason'     => $reason === 'Other' ? $otherReason : null,
            'description'      => $description,
            'evidence_path'    => $evidencePath,
            'status'           => 'pending',
        ];

        if (!$this->reportModel->save($reportData)) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to submit report. Please try again.'
            ]);
        }

        return $this->response->setStatusCode(201)->setJSON([
            'status'  => 'success',
            'message' => 'Thank you for reporting this listing. Our team will review it shortly.',
            'report_id' => $this->reportModel->getInsertID()
        ]);
    }

    /**
     * Submit a message report
     */
    public function submitMessageReport(): ResponseInterface
    {
        // Check if user is logged in
        $userId = $this->getCurrentUserId();
        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'You must be logged in to report a message.'
            ]);
        }

        // Get request data
        $messageId = (int) $this->request->getPost('message_id');
        $reason = $this->request->getPost('reason');
        $otherReason = $this->request->getPost('other_reason');
        $description = $this->request->getPost('description');

        // Validate message exists
        $message = $this->messageModel->find($messageId);
        if (!$message) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Message not found.'
            ]);
        }

        // Prevent reporting own messages
        if ($message['sender_id'] == $userId) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'You cannot report your own message.'
            ]);
        }

        // Validate user is in the message session
        $session = $this->sessionModel->find($message['session_id']);
        if (!$session || ($session['buyer_id'] != $userId && $session['seller_id'] != $userId)) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'You are not authorized to report this message.'
            ]);
        }

        // Validate reason
        $validReasons = ReportsModel::getMessageReasons();
        if (!in_array($reason, $validReasons)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Invalid reason selected.'
            ]);
        }

        // If reason is "Other", require other_reason
        if ($reason === 'Other' && empty($otherReason)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Please describe the issue when selecting "Other".'
            ]);
        }

        // Check for duplicate pending reports
        if ($this->reportModel->hasDuplicateReport($userId, 'message', $messageId)) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 'error',
                'message' => 'You have already reported this message. Thank you for your report.'
            ]);
        }

        // Sanitize description
        $description = empty($description) ? null : substr($description, 0, 1000);

        // Determine reported user
        $reportedUserId = ($message['sender_id'] != $userId) ? $message['sender_id'] : null;

        // Create report
        $reportData = [
            'report_type'      => 'message',
            'reporter_user_id' => $userId,
            'reported_user_id' => $reportedUserId,
            'message_id'       => $messageId,
            'session_id'       => $message['session_id'],
            'reason'           => $reason,
            'other_reason'     => $reason === 'Other' ? $otherReason : null,
            'description'      => $description,
            'status'           => 'pending',
        ];

        if (!$this->reportModel->save($reportData)) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to submit report. Please try again.'
            ]);
        }

        return $this->response->setStatusCode(201)->setJSON([
            'status'  => 'success',
            'message' => 'Thank you for reporting this message. Our team will review it shortly.',
            'report_id' => $this->reportModel->getInsertID()
        ]);
    }
}
