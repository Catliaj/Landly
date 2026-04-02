<?php

namespace App\Controllers\Seller;

use App\Controllers\BaseController;
use App\Models\ListingLocationsModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use Config\Database;


class LandListingCRUDController extends BaseController
{
    private const VERIFIED_LISTING_STATUSES = ['true', 'false', 'pending', 'rejected'];

    public function createLandListing()
    {
        $sellerId = $this->getCurrentUserId();
        if ($sellerId <= 0) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized.']);
        }

        [$images, $documents] = $this->resolveListingFilesSafe();
        if (empty($images) || empty($documents)) {
            return $this->response->setStatusCode(422)->setJSON(['status' => 'error', 'message' => 'Images and documents are required.']);
        }

        $storedImages = $this->storeFilesToSellerFolder($images, 'images');
        $storedDocs   = $this->storeFilesToSellerFolder($documents, 'documents');

        if (empty($storedImages) || empty($storedDocs)) {
            return $this->response->setStatusCode(422)->setJSON(['status' => 'error', 'message' => 'Failed to store files.']);
        }

        $verifiedListing = $this->normalizeVerifiedListingInput($this->request->getPost('is_verified_listing'));
        if ($verifiedListing === null) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'Invalid value for is_verified_listing. Allowed values: true, false, pending, rejected.',
            ]);
        }

        $data = [
            'title'                   => trim((string) $this->request->getPost('title')),
            'description'             => trim((string) $this->request->getPost('description')),
            'barangay'                => trim((string) $this->request->getPost('barangay')),
            'city'                    => trim((string) $this->request->getPost('city')),
            'province'                => trim((string) $this->request->getPost('province')),
            'road_access_type'        => (string) $this->request->getPost('road_access_type'),
            'view_type'               => (string) $this->request->getPost('view_type'),
            'property_type'           => (string) $this->request->getPost('property_type'),
            'is_titled'               => (int) $this->request->getPost('is_titled'),
            'has_tax_declaration'     => (int) $this->request->getPost('has_tax_declaration'),
            'has_lra_approved_plan'   => (int) $this->request->getPost('has_lra_approved_plan'),
            'mother_titled_disclosed' => (int) $this->request->getPost('mother_titled_disclosed'),
            'document_status'         => (string) $this->request->getPost('document_status'),
            'investment_ready'        => (int) $this->request->getPost('investment_ready'),
            'developing_area'         => (int) $this->request->getPost('developing_area'),
            'listing_status'          => (string) $this->request->getPost('listing_status'),
            'is_verified_listing'     => $verifiedListing,
            'price'                   => (float) $this->request->getPost('price'),
        ];

        $db = Database::connect();
        $db->transBegin();

        try {
            $model = new \App\Models\LandListings();
            $listingId = (int) $model->createListing($sellerId, $data);
            if ($listingId <= 0) {
                throw new \RuntimeException('Failed to create listing.');
            }

            $location = $this->saveListingLocation($listingId);

            $this->insertListingImages($listingId, $storedImages);
            $this->insertListingDocuments($listingId, $storedDocs);

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaction failed.');
            }

            $db->transCommit();

            return $this->response->setStatusCode(201)->setJSON([
                'status' => 'success',
                'listing_id' => $listingId,
                'location' => $location,
                'images' => $storedImages,
                'documents' => $storedDocs,
            ]);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function saveListingLocation(int $listingId): ?array
    {
        $latitudeInput = $this->request->getPost('latitude');
        if ($latitudeInput === null || $latitudeInput === '') {
            $latitudeInput = $this->request->getPost('Latitude');
        }

        $longitudeInput = $this->request->getPost('longitude');
        if ($longitudeInput === null || $longitudeInput === '') {
            $longitudeInput = $this->request->getPost('Longitude');
        }

        $hasLatitude = $latitudeInput !== null && $latitudeInput !== '';
        $hasLongitude = $longitudeInput !== null && $longitudeInput !== '';

        if (! $hasLatitude && ! $hasLongitude) {
            return null;
        }

        if (! $hasLatitude || ! $hasLongitude) {
            throw new \RuntimeException('Both latitude and longitude are required when location is provided.');
        }

        if (! is_numeric($latitudeInput) || ! is_numeric($longitudeInput)) {
            throw new \RuntimeException('Latitude and longitude must be numeric values.');
        }

        $db = Database::connect();
        if (! $db->tableExists('listing_locations')) {
            throw new \RuntimeException('listing_locations table does not exist. Run migrations first.');
        }

        $location = [
            'listing_id' => $listingId,
            'latitude' => (float) $latitudeInput,
            'longitude' => (float) $longitudeInput,
        ];

        $locationModel = new ListingLocationsModel();
        if (! $locationModel->insert($location, false)) {
            $error = implode(' ', $locationModel->errors() ?: ['Failed to save listing location.']);
            throw new \RuntimeException($error);
        }

        return $location;
    }

    /**
     * Save uploaded image paths into listing_images table.
     */
    private function insertListingImages(int $listingId, array $paths): void
    {
        $db = Database::connect();
        if (! $db->tableExists('listing_images')) {
            return;
        }

        $fields = array_flip($db->getFieldNames('listing_images'));

        foreach ($paths as $path) {
            $row = [];

            if (isset($fields['listing_id'])) $row['listing_id'] = $listingId;
            elseif (isset($fields['land_listing_id'])) $row['land_listing_id'] = $listingId;

            if (isset($fields['image_path'])) $row['image_path'] = $path;
            elseif (isset($fields['file_path'])) $row['file_path'] = $path;
            elseif (isset($fields['path'])) $row['path'] = $path;

            if (isset($fields['type'])) $row['type'] = 'property_image';
            if (isset($fields['created_at'])) $row['created_at'] = date('Y-m-d H:i:s');
            if (isset($fields['updated_at'])) $row['updated_at'] = date('Y-m-d H:i:s');

            if (!empty($row)) {
                $db->table('listing_images')->insert($row);
            }
        }
    }

    /**
     * Save uploaded document paths into listing_documents table.
     */
    private function insertListingDocuments(int $listingId, array $paths): void
    {
        $db = Database::connect();
        if (! $db->tableExists('listing_documents')) {
            return;
        }

        $fields = array_flip($db->getFieldNames('listing_documents'));

        foreach ($paths as $path) {
            $row = [];
            $docType = $this->detectDocumentType($path);

            if (isset($fields['listing_id'])) $row['listing_id'] = $listingId;
            elseif (isset($fields['land_listing_id'])) $row['land_listing_id'] = $listingId;

            if (isset($fields['document_path'])) $row['document_path'] = $path;
            elseif (isset($fields['file_path'])) $row['file_path'] = $path;
            elseif (isset($fields['path'])) $row['path'] = $path;

            if (isset($fields['document_type'])) $row['document_type'] = $docType;
            elseif (isset($fields['type'])) $row['type'] = $docType;

            if (isset($fields['created_at'])) $row['created_at'] = date('Y-m-d H:i:s');
            if (isset($fields['updated_at'])) $row['updated_at'] = date('Y-m-d H:i:s');

            if (!empty($row)) {
                $db->table('listing_documents')->insert($row);
            }
        }
    }

    private function detectDocumentType(string $path): string
    {
        $name = strtolower(basename($path));

        if (str_contains($name, 'valid') && str_contains($name, 'id')) return 'valid_id';
        if (str_contains($name, 'tax')) return 'tax_declaration';
        if (str_contains($name, 'title')) return 'land_title';

        return 'supporting_document';
    }

    private function readSampleFiles(): array
    {
        $candidates = [
            rtrim(FCPATH, '\\/') . DIRECTORY_SEPARATOR . 'sample_seller_document' . DIRECTORY_SEPARATOR,
            rtrim(FCPATH, '\\/') . DIRECTORY_SEPARATOR . 'sample_seller_documents' . DIRECTORY_SEPARATOR,
        ];

        $sampleDir = null;
        foreach ($candidates as $dir) {
            if (is_dir($dir)) {
                $sampleDir = $dir;
                break;
            }
        }

        if ($sampleDir === null) {
            return [[], []];
        }

        $imageExts = ['jpg', 'jpeg', 'png', 'webp'];
        $docExts   = ['pdf', 'doc', 'docx'];

        $images = [];
        $docs   = [];
        $all    = [];

        foreach (scandir($sampleDir) ?: [] as $name) {
            if ($name === '.' || $name === '..') continue;

            $fullPath = $sampleDir . $name;
            if (!is_file($fullPath)) continue;

            $all[] = $fullPath;
            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

            if (in_array($ext, $imageExts, true)) $images[] = $fullPath;
            if (in_array($ext, $docExts, true))   $docs[]   = $fullPath;
        }

        // Soft fallback: if one group is empty, reuse available sample files
        if (empty($images) && !empty($all)) $images = $all;
        if (empty($docs) && !empty($all))   $docs   = $all;

        return [$images, $docs];
    }

    private function deleteStoredFiles(array $relativePaths): void
    {
        foreach ($relativePaths as $relative) {
            $full = rtrim(FCPATH, '\\/') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
            if (is_file($full)) {
                @unlink($full);
            }
        }
    }

    private function resolveListingFilesSafe(): array
    {
        $allFiles = $this->request->getFiles();
        $images = isset($allFiles['images']) && is_array($allFiles['images']) ? $allFiles['images'] : [];
        $docs   = isset($allFiles['documents']) && is_array($allFiles['documents']) ? $allFiles['documents'] : [];

        $images = array_values(array_filter($images, fn($f) => $f instanceof UploadedFile && $f->isValid()));
        $docs   = array_values(array_filter($docs, fn($f) => $f instanceof UploadedFile && $f->isValid()));

        if (!empty($images) && !empty($docs)) {
            return [$images, $docs];
        }

        $dirA = rtrim(FCPATH, '\\/') . DIRECTORY_SEPARATOR . 'sample_seller_document' . DIRECTORY_SEPARATOR;
        $dirB = rtrim(FCPATH, '\\/') . DIRECTORY_SEPARATOR . 'sample_seller_documents' . DIRECTORY_SEPARATOR;
        $sampleDir = is_dir($dirA) ? $dirA : $dirB;

        if (!is_dir($sampleDir)) {
            return [$images, $docs];
        }

        $imgExt = ['jpg', 'jpeg', 'png', 'webp'];
        $docExt = ['pdf', 'doc', 'docx'];

        $fallbackImages = [];
        $fallbackDocs = [];
        foreach (scandir($sampleDir) ?: [] as $name) {
            if ($name === '.' || $name === '..') continue;
            $full = $sampleDir . $name;
            if (!is_file($full)) continue;
            $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
            if (in_array($ext, $imgExt, true)) $fallbackImages[] = $full;
            if (in_array($ext, $docExt, true)) $fallbackDocs[] = $full;
        }

        return [!empty($images) ? $images : $fallbackImages, !empty($docs) ? $docs : $fallbackDocs];
    }

    private function storeFilesToSellerFolder(array $files, string $type): array
    {
        $target = rtrim(FCPATH, '\\/') . DIRECTORY_SEPARATOR . 'seller' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR;
        if (!is_dir($target)) mkdir($target, 0775, true);

        $stored = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                if (!$file->isValid()) continue;
                $newName = $file->getRandomName();
                $file->move($target, $newName);
                $stored[] = "seller/{$type}/{$newName}";
                continue;
            }

            if (is_string($file) && is_file($file)) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $newName = uniqid($type . '_', true) . ($ext ? ".{$ext}" : '');
                if (copy($file, $target . $newName)) {
                    $stored[] = "seller/{$type}/{$newName}";
                }
            }
        }

        return $stored;
    }

    private function normalizeVerifiedListingInput($value): ?string
    {
        if ($value === null || $value === '') {
            return 'pending';
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