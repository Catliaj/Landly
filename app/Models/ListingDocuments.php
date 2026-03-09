<?php

namespace App\Models;

use CodeIgniter\Model;

class ListingDocuments extends Model
{
    protected $table            = 'listing_documents';
    protected $primaryKey       = 'document_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'listing_id',
        'document_type',
        'file_path',
        'is_verified',
        'updated_at'
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

    public function addDocument($listingId, $documentType, $filePath)
    {
        $data = [
            'listing_id' => $listingId,
            'document_type' => $documentType,
            'file_path' => $filePath,
            'is_verified' => false,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->insert($data);
    }

    public function verifyDocument($documentId)
    {
        return $this->update($documentId, ['is_verified' => true, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    public function getDocumentsByListing($listingId)
    {
        return $this->where('listing_id', $listingId)->findAll();
    }

    public function getAllDocumentsIsNotVerified()
    {
        return $this->where('is_verified', false)->findAll();
    }
    


}
