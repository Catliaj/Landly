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
        'reported_by',
        'listing_id',
        'reason',
        'description',
        'status',
        'admin_notes',
        'created_at',
        'resolved_at',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = null;
    protected $deletedField  = null;

    public function getPendingReports()
    {
        return $this->where('status', 'pending')->countAllResults();
    }

    public function getReportsByStatus($status)
    {
        return $this->where('status', $status)->findAll();
    }
}
