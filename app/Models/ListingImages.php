<?php

namespace App\Models;

use CodeIgniter\Model;

class ListingImages extends Model
{
    protected $table = 'listing_images';
    protected $primaryKey = 'image_id';

    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;

    protected $allowedFields = [
        'listing_id',
        'image_path',
        'is_primary'
    ];

    // timestamps
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /*
    |--------------------------------------------------------------------------
    | ADD IMAGE
    |--------------------------------------------------------------------------
    */

    public function addImage($listingId, $imagePath, $isPrimary = false)
    {
        $data = [
            'listing_id' => $listingId,
            'image_path' => $imagePath,
            'is_primary' => $isPrimary ? 1 : 0
        ];

        $this->insert($data);

        return $this->getInsertID();
    }

    /*
    |--------------------------------------------------------------------------
    | GET IMAGES BY LISTING
    |--------------------------------------------------------------------------
    */

    public function getImagesByListingId($listingId)
    {
        return $this->where('listing_id', $listingId)->findAll();
    }

    /*
    |--------------------------------------------------------------------------
    | SET PRIMARY IMAGE
    |--------------------------------------------------------------------------
    */

    public function setPrimaryImage($imageId)
    {
        $image = $this->find($imageId);

        if (!$image) {
            return false;
        }

        // remove previous primary image
        $this->where('listing_id', $image['listing_id'])
             ->set(['is_primary' => 0])
             ->update();

        // set selected image as primary
        return $this->update($imageId, [
            'is_primary' => 1
        ]);
    }
}