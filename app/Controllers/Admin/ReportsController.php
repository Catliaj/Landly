<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ReportsModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class ReportsController extends BaseController
{
    protected $reportModel;
    protected $userModel;

    public function __construct()
    {
        $this->reportModel = new ReportsModel();
        $this->userModel = new UserModel();
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

        if ($status && in_array($status, ['pending', 'reviewed', 'dismissed', 'action_taken'])) {
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
                'status' => 'error',
                'message' => 'Report not found'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $report
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
        $validStatuses = ['pending', 'reviewed', 'dismissed', 'action_taken'];
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
