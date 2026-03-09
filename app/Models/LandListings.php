<?php

namespace App\Models;

use CodeIgniter\Model;

class LandListings extends Model
{
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
}