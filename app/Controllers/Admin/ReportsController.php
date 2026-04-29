<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ReportsModel;
use App\Models\UserModel;
use App\Models\LandListings;
use CodeIgniter\HTTP\ResponseInterface;

class ReportsController extends BaseController
{
    protected $reportModel;
    protected $userModel;
    protected $landListingsModel;

    public function __construct()
    {
        $this->reportModel = new ReportsModel();
        $this->userModel = new UserModel();
        $this->landListingsModel = new LandListings();
    }

    /**
     * List all reports with filtering
     */
    public function listReports(): ResponseInterface
    {
        // Check admin authorization
        if (!$this->isAdmin()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized'
            ]);
        }

        $type = $this->request->getGet('type'); // listing or message
        $status = $this->request->getGet('status'); // pending, reviewed, dismissed, action_taken
        $page = (int) ($this->request->getGet('page') ?? 1);
        $limit = 20;

        $query = $this->reportModel;

        if ($type && in_array($type, ['listing', 'message'])) {
            $query = $query->where('report_type', $type);
        }

        if ($status && in_array($status, ['pending', 'reviewed', 'dismissed', 'action_taken', 'suspended'])) {
            $query = $query->where('status', $status);
        }

        $total = $query->countAllResults();
        $reports = $this->reportModel->getReportsWithDetails();

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $reports,
            'total'  => $total,
            'page'   => $page,
            'limit'  => $limit
        ]);
    }

    /**
     * Get report details
     */
    public function getReportDetail(int $reportId): ResponseInterface
    {
        // Check admin authorization
        if (!$this->isAdmin()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized'
            ]);
        }

        $report = $this->reportModel->getReportDetail($reportId);

        if (!$report) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'status' => 'error',
                'message' => 'Report not found'
            ]);
        }

        $report = $this->enrichReportDetail($report);

        return $this->response->setJSON([
            'success' => true,
            'status' => 'success',
            'data'   => $report,
            'report' => $report
        ]);
    }

    /**
     * Update report status and add admin notes
     */
    public function updateReportStatus(int $reportId): ResponseInterface
    {
        // Check admin authorization
        if (!$this->isAdmin()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized'
            ]);
        }

        // Check report exists
        $report = $this->reportModel->find($reportId);
        if (!$report) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Report not found'
            ]);
        }

        // Get request data
        $status = $this->request->getPost('status');
        $adminNotes = $this->request->getPost('admin_notes');

        // Validate status
        $validStatuses = ['pending', 'reviewed', 'dismissed', 'action_taken', 'suspended'];
        if (!in_array($status, $validStatuses)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Invalid status'
            ]);
        }

        // Update report
        $updateData = [
            'status'      => $status,
            'admin_notes' => $adminNotes ? substr($adminNotes, 0, 5000) : null,
            'reviewed_by' => $this->getCurrentUserId(),
            'reviewed_at' => date('Y-m-d H:i:s'),
        ];

        if (!$this->reportModel->update($reportId, $updateData)) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to update report'
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Report updated successfully'
        ]);
    }

    /**
     * Suspend report and related targets (user and/or listing)
     */
    public function suspendReportAndTargets(int $reportId): ResponseInterface
    {
        // Check admin authorization
        if (!$this->isAdmin()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized'
            ]);
        }

        // Check report exists
        $report = $this->reportModel->find($reportId);
        if (!$report) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Report not found'
            ]);
        }

        $adminNotes = $this->request->getPost('admin_notes');

        // Update report status to suspended
        $updateData = [
            'status'      => 'suspended',
            'admin_notes' => $adminNotes ? substr($adminNotes, 0, 5000) : null,
            'reviewed_by' => $this->getCurrentUserId(),
            'reviewed_at' => date('Y-m-d H:i:s'),
        ];

        if (!$this->reportModel->update($reportId, $updateData)) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to update report'
            ]);
        }

        // Suspend the reported user (if exists)
        if (!empty($report['reported_user_id'])) {
            $this->userModel->update($report['reported_user_id'], ['is_active' => 0]);
        }

        // If listing report, suspend the listing verification state
        if ($report['report_type'] === 'listing' && !empty($report['listing_id'])) {
            $this->landListingsModel->update($report['listing_id'], [
                'is_verified_listing' => 'suspended',
                'listing_status' => 'closed',
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Report suspended and targets have been suspended'
        ]);
    }

    /**
     * Get report statistics
     */
    public function getStatistics(): ResponseInterface
    {
        // Check admin authorization
        if (!$this->isAdmin()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized'
            ]);
        }

        $db = \Config\Database::connect();

        $stats = [
            'total_pending'        => $this->reportModel->where('status', 'pending')->countAllResults(),
            'total_reviewed'       => $this->reportModel->where('status', 'reviewed')->countAllResults(),
            'total_dismissed'      => $this->reportModel->where('status', 'dismissed')->countAllResults(),
            'total_action_taken'   => $this->reportModel->where('status', 'action_taken')->countAllResults(),
            'total_suspended'      => $this->reportModel->where('status', 'suspended')->countAllResults(),
            'listing_reports'      => $this->reportModel->where('report_type', 'listing')->countAllResults(),
            'message_reports'      => $this->reportModel->where('report_type', 'message')->countAllResults(),
            'pending_by_reason'    => $db->table('reports')
                ->select(['reason', 'COUNT(*) as count'])
                ->where('status', 'pending')
                ->groupBy('reason')
                ->get()
                ->getResultArray(),
        ];

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $stats
        ]);
    }

    /**
     * Normalize a report detail payload for admin views.
     */
    protected function enrichReportDetail(array $report): array
    {
        $reporterName = trim((string) ($report['reporter_first_name'] ?? '') . ' ' . (string) ($report['reporter_last_name'] ?? ''));
        if ($reporterName === '') {
            $reporterName = 'Unknown User';
        }

        $reportedName = trim((string) ($report['reported_first_name'] ?? '') . ' ' . (string) ($report['reported_last_name'] ?? ''));
        $targetName = $reportedName;

        if (($report['report_type'] ?? '') === 'listing') {
            $targetName = trim((string) ($report['listing_title'] ?? ''));
            if ($targetName === '' && $reportedName !== '') {
                $targetName = $reportedName;
            }
        } elseif (($report['report_type'] ?? '') === 'message') {
            $targetName = $reportedName !== '' ? $reportedName : 'Message #' . (string) ($report['message_id'] ?? '');
        }

        if ($targetName === '') {
            $targetName = 'Unknown Target';
        }

        $report['reported_by_name'] = $reporterName;
        $report['reported_against_name'] = $targetName;
        $report['subject'] = $this->formatReportSubject($report);

        return $report;
    }

    /**
     * Build a human-readable report subject from the stored fields.
     */
    protected function formatReportSubject(array $report): string
    {
        $type = ucfirst((string) ($report['report_type'] ?? 'Report'));
        $reason = trim((string) ($report['reason'] ?? ''));
        $otherReason = trim((string) ($report['other_reason'] ?? ''));

        if ($reason === 'Other' && $otherReason !== '') {
            $reason = $otherReason;
        }

        return trim($type . ($reason !== '' ? ': ' . $reason : ' Report'));
    }

    /**
     * Check if current user is admin
     */
    protected function isAdmin(): bool
    {
        $userId = $this->getCurrentUserId();
        if ($userId <= 0) {
            return false;
        }

        $user = $this->userModel->find($userId);
        return $user && isset($user['roles']) && strpos($user['roles'], 'admin') !== false;
    }
}
