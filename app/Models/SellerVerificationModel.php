<?php

namespace App\Models;

use CodeIgniter\Model;

class SellerVerificationModel extends Model
{
    protected $table            = 'seller_verification_documents';
    protected $primaryKey       = 'document_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'seller_id',
        'document_type',
        'file_path',
        'is_verified',
        'reviewed_by',
        'reviewed_at',
        'uploaded_at',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function addSellerDocument($sellerId, $documentType, $filePath)
    {
        $data = [
            'seller_id' => $sellerId,
            'document_type' => $documentType,
            'file_path' => $filePath,
            'is_verified' => 0,
            'uploaded_at' => date('Y-m-d H:i:s'),
        ];

        return $this->insert($data);
    }

    public function verifyDocument($documentId, $reviewerId)
    {
        $data = [
            'is_verified' => 1,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => date('Y-m-d H:i:s'),
        ];

        return $this->update($documentId, $data);
    }
}


