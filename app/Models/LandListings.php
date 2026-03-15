<?php

namespace App\Models;

use CodeIgniter\Model;

class LandListings extends Model
{
    private const VERIFIED_LISTING_STATUSES = ['true', 'false', 'pending', 'rejected'];

    protected $table = 'land_listings';
    protected $primaryKey = 'listing_id';

    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;

    protected $allowedFields = [
        'seller_id',
        'title',
        'description',
        'barangay',
        'city',
        'province',
        'road_access_type',
        'view_type',
        'property_type',
        'is_titled',
        'has_tax_declaration',
        'has_lra_approved_plan',
        'mother_titled_disclosed',
        'document_status',
        'investment_ready',
        'developing_area',
        'listing_status',
        'is_verified_listing',
        'price'
    ];

    // timestamps
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'is_verified_listing' => 'required|in_list[true,false,pending,rejected]',
    ];
    protected $skipValidation = false;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['normalizeVerifiedListingBeforeInsert'];
    protected $beforeUpdate   = ['normalizeVerifiedListingBeforeUpdate'];

    /*
    |--------------------------------------------------------------------------
    | CRUD METHODS
    |--------------------------------------------------------------------------
    */

    public function createListing($sellerId, $data)
    {
        $data['seller_id'] = $sellerId;

        $this->insert($data);

        return $this->getInsertID();
    }

    public function getListingById($id)
    {
        return $this->find($id);
    }

    public function updateListing($id, $data)
    {
        return $this->update($id, $data);
    }

    public function deleteListing($id)
    {
        return $this->delete($id);
    }

    public function getListingsBySeller($sellerId)
    {
        return $this->where('seller_id', $sellerId)->findAll();
    }

    public function getListingsByStatus($status)
    {
        return $this->where('listing_status', $status)->findAll();
    }

    protected function normalizeVerifiedListingBeforeInsert(array $data): array
    {
        $verifiedValue = $data['data']['is_verified_listing'] ?? null;
        $normalized = $this->normalizeVerifiedListingValue($verifiedValue, true);

        if ($normalized !== null) {
            $data['data']['is_verified_listing'] = $normalized;
        }

        return $data;
    }

    protected function normalizeVerifiedListingBeforeUpdate(array $data): array
    {
        if (! array_key_exists('is_verified_listing', $data['data'] ?? [])) {
            return $data;
        }

        $normalized = $this->normalizeVerifiedListingValue($data['data']['is_verified_listing'], false);

        if ($normalized !== null) {
            $data['data']['is_verified_listing'] = $normalized;
        }

        return $data;
    }

    private function normalizeVerifiedListingValue($value, bool $useDefaultWhenEmpty): ?string
    {
        if ($value === null || $value === '') {
            return $useDefaultWhenEmpty ? 'pending' : null;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_int($value) || is_float($value)) {
            return ((int) $value) === 1 ? 'true' : 'false';
        }

        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, self::VERIFIED_LISTING_STATUSES, true)) {
            return $normalized;
        }

        return match ($normalized) {
            '1', 'yes', 'verified' => 'true',
            '0', 'no', 'unverified' => 'false',
            default => null,
        };
    }
}