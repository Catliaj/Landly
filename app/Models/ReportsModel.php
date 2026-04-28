<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportsModel extends Model
{
    protected $table            = 'reports';
    protected $primaryKey       = 'report_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'report_type',
        'reporter_user_id',
        'reported_user_id',
        'listing_id',
        'message_id',
        'session_id',
        'inquiry_id',
        'reason',
        'other_reason',
        'description',
        'evidence_path',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'created_at',
        'updated_at',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = null;

    /**
     * Validation rules for reports
     */
    protected $validationRules = [
        'report_type'      => 'required|in_list[listing,message]',
        'reporter_user_id' => 'required|integer|greater_than[0]',
        'reason'           => 'required|string',
        'listing_id'       => 'permit_empty|integer',
        'message_id'       => 'permit_empty|integer',
    ];

    protected $validationMessages = [];

    /**
     * Get pending reports
     */
    public function getPendingReports()
    {
        return $this->where('status', 'pending')->countAllResults();
    }

    /**
     * Get reports by status
     */
    public function getReportsByStatus($status)
    {
        return $this->where('status', $status)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get all reports with user and listing/message details
     */
    public function getReportsWithDetails()
    {
        $db = \Config\Database::connect();
        return $db->table('reports')
            ->select([
                'reports.*',
                'reporter.first_name as reporter_first_name',
                'reporter.last_name as reporter_last_name',
                'reporter.email as reporter_email',
                'reported.first_name as reported_first_name',
                'reported.last_name as reported_last_name',
                'reported.email as reported_email',
                'listings.title as listing_title',
                'listings.seller_id',
                'messages.message_text',
                'messages.sender_id',
            ])
            ->join('users as reporter', 'reporter.user_id = reports.reporter_user_id', 'left')
            ->join('users as reported', 'reported.user_id = reports.reported_user_id', 'left')
            ->join('land_listings as listings', 'listings.listing_id = reports.listing_id', 'left')
            ->join('messages', 'messages.message_id = reports.message_id', 'left')
            ->orderBy('reports.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get reports for a specific listing
     */
    public function getListingReports($listingId)
    {
        return $this->where('report_type', 'listing')
            ->where('listing_id', $listingId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get reports for a specific message
     */
    public function getMessageReports($messageId)
    {
        return $this->where('report_type', 'message')
            ->where('message_id', $messageId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Check if a duplicate report exists (same reporter, same target, pending status)
     */
    public function hasDuplicateReport($reporterUserId, $type, $targetId)
    {
        $field = ($type === 'listing') ? 'listing_id' : 'message_id';
        
        return $this->where('report_type', $type)
            ->where('reporter_user_id', $reporterUserId)
            ->where($field, $targetId)
            ->where('status', 'pending')
            ->countAllResults() > 0;
    }

    /**
     * Get reason options for listing reports
     */
    public static function getListingReasons()
    {
        return [
            'Fake listing',
            'Wrong price',
            'Misleading photos',
            'Invalid documents',
            'Duplicate listing',
            'Suspicious seller',
            'Other',
        ];
    }

    /**
     * Get reason options for message reports
     */
    public static function getMessageReasons()
    {
        return [
            'Harassment',
            'Scam or fraud',
            'Spam',
            'Offensive content',
            'Fake information',
            'Unsafe payment request',
            'Other',
        ];
    }

    /**
     * Get report detail with all related information
     */
    public function getReportDetail($reportId)
    {
        $db = \Config\Database::connect();
        return $db->table('reports')
            ->select([
                'reports.*',
                'reporter.first_name as reporter_first_name',
                'reporter.last_name as reporter_last_name',
                'reporter.email as reporter_email',
                'reported.first_name as reported_first_name',
                'reported.last_name as reported_last_name',
                'reported.email as reported_email',
                'listings.title as listing_title',
                'listings.description as listing_description',
                'listings.seller_id',
                'messages.message_text',
                'messages.sender_id',
                'messages.sent_at as message_sent_at',
            ])
            ->join('users as reporter', 'reporter.user_id = reports.reporter_user_id', 'left')
            ->join('users as reported', 'reported.user_id = reports.reported_user_id', 'left')
            ->join('land_listings as listings', 'listings.listing_id = reports.listing_id', 'left')
            ->join('messages', 'messages.message_id = reports.message_id', 'left')
            ->where('reports.report_id', $reportId)
            ->get()
            ->getRowArray();
    }
}
