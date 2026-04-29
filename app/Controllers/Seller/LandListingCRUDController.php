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

    public function readLandListing(int $listingId)
    {
        $sellerId = $this->getCurrentUserId();
        if ($sellerId <= 0) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized.']);
        }

        $model = new \App\Models\LandListings();
        $listing = $model
            ->where('listing_id', $listingId)
            ->where('seller_id', $sellerId)
            ->first();

        if (! is_array($listing)) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Listing not found.']);
        }

        $location = $this->getListingLocation($listingId);
        if ($location !== null) {
            $listing['latitude'] = (float) ($location['latitude'] ?? 0);
            $listing['longitude'] = (float) ($location['longitude'] ?? 0);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'listing' => $listing,
        ]);
    }

    public function updateLandListing(int $listingId)
    {
        $sellerId = $this->getCurrentUserId();
        if ($sellerId <= 0) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized.']);
        }

        $model = new \App\Models\LandListings();
        $existing = $model
            ->where('listing_id', $listingId)
            ->where('seller_id', $sellerId)
            ->first();

        if (! is_array($existing)) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Listing not found.']);
        }

        $input = $this->request->getJSON(true);
        if (! is_array($input) || $input === []) {
            $input = $this->request->getRawInput();
        }
        if (! is_array($input) || $input === []) {
            $input = $this->request->getPost();
        }

        $titleStatus = strtolower(trim((string) ($input['title_status'] ?? '')));

        $updateData = [
            'title' => trim((string) ($input['title'] ?? ($existing['title'] ?? ''))),
            'description' => trim((string) ($input['description'] ?? ($existing['description'] ?? ''))),
            'barangay' => trim((string) ($input['barangay'] ?? ($existing['barangay'] ?? ''))),
            'city' => trim((string) ($input['city'] ?? ($existing['city'] ?? ''))),
            'province' => trim((string) ($input['province'] ?? ($existing['province'] ?? ''))),
            'road_access_type' => (string) ($input['road_access_type'] ?? ($existing['road_access_type'] ?? '')),
            'view_type' => (string) ($input['view_type'] ?? ($existing['view_type'] ?? 'none')),
            'property_type' => (string) ($input['property_type'] ?? ($existing['property_type'] ?? '')),
            'investment_ready' => (int) ($input['investment_ready'] ?? ($existing['investment_ready'] ?? 0)),
            'developing_area' => (int) ($input['developing_area'] ?? ($existing['developing_area'] ?? 0)),
            'price' => (float) ($input['price'] ?? ($existing['price'] ?? 0)),
        ];

        if ($titleStatus === 'clean') {
            $updateData['is_titled'] = 1;
            $updateData['has_tax_declaration'] = 0;
            $updateData['document_status'] = 'complete';
        } elseif ($titleStatus === 'tax-declaration') {
            $updateData['is_titled'] = 0;
            $updateData['has_tax_declaration'] = 1;
            $updateData['document_status'] = 'partial';
        } elseif ($titleStatus === 'untitled') {
            $updateData['is_titled'] = 0;
            $updateData['has_tax_declaration'] = 0;
            $updateData['document_status'] = 'pending';
        }

        if ($updateData['title'] === '' || $updateData['property_type'] === '' || $updateData['city'] === '' || $updateData['province'] === '' || $updateData['road_access_type'] === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'Please fill in all required fields.',
            ]);
        }

        if ($updateData['price'] <= 0) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'Price must be greater than zero.',
            ]);
        }

        $db = Database::connect();
        $db->transBegin();

        try {
            $updated = $model->update($listingId, $updateData);
            if (! $updated) {
                $error = implode(' ', $model->errors() ?: ['Unable to update listing.']);
                throw new \RuntimeException($error);
            }

            $this->upsertListingLocation($listingId, $input);

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaction failed while updating listing.');
            }

            $db->transCommit();

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Listing updated successfully.',
            ]);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function deleteLandListing(int $listingId)
    {
        $sellerId = $this->getCurrentUserId();
        if ($sellerId <= 0) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized.']);
        }

        $model = new \App\Models\LandListings();
        $existing = $model
            ->where('listing_id', $listingId)
            ->where('seller_id', $sellerId)
            ->first();

        if (! is_array($existing)) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Listing not found.']);
        }

        $db = Database::connect();
        $db->transBegin();

        try {
            $imagePaths = $this->getStoredPathsFromTable('listing_images', $listingId, ['image_path', 'file_path', 'path']);
            $documentPaths = $this->getStoredPathsFromTable('listing_documents', $listingId, ['document_path', 'file_path', 'path']);

            $this->deleteRowsByListingId('listing_locations', $listingId);
            $this->deleteRowsByListingId('listing_images', $listingId);
            $this->deleteRowsByListingId('listing_documents', $listingId);

            $deleted = $model->delete($listingId);
            if (! $deleted) {
                throw new \RuntimeException('Unable to delete listing.');
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaction failed while deleting listing.');
            }

            $db->transCommit();

            $this->deleteStoredFiles(array_merge($imagePaths, $documentPaths));

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Listing deleted successfully.',
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

    private function getListingLocation(int $listingId): ?array
    {
        $db = Database::connect();
        if (! $db->tableExists('listing_locations')) {
            return null;
        }

        $row = $db->table('listing_locations')
            ->where('listing_id', $listingId)
            ->get()
            ->getRowArray();

        return is_array($row) ? $row : null;
    }

    private function upsertListingLocation(int $listingId, array $input): void
    {
        $latitudeInput = $input['latitude'] ?? $input['Latitude'] ?? null;
        $longitudeInput = $input['longitude'] ?? $input['Longitude'] ?? null;

        if ($latitudeInput === null || $latitudeInput === '' || $longitudeInput === null || $longitudeInput === '') {
            return;
        }

        if (! is_numeric($latitudeInput) || ! is_numeric($longitudeInput)) {
            return;
        }

        $db = Database::connect();
        if (! $db->tableExists('listing_locations')) {
            return;
        }

        $table = $db->table('listing_locations');
        $existing = $table->where('listing_id', $listingId)->get()->getRowArray();

        $payload = [
            'listing_id' => $listingId,
            'latitude' => (float) $latitudeInput,
            'longitude' => (float) $longitudeInput,
        ];

        if (is_array($existing)) {
            $table->where('listing_id', $listingId)->update([
                'latitude' => $payload['latitude'],
                'longitude' => $payload['longitude'],
            ]);
            return;
        }

        $table->insert($payload);
    }

    private function getStoredPathsFromTable(string $tableName, int $listingId, array $pathFieldCandidates): array
    {
        $db = Database::connect();
        if (! $db->tableExists($tableName)) {
            return [];
        }

        $fields = $db->getFieldNames($tableName);
        $listingField = in_array('listing_id', $fields, true)
            ? 'listing_id'
            : (in_array('land_listing_id', $fields, true) ? 'land_listing_id' : null);

        if ($listingField === null) {
            return [];
        }

        $pathField = null;
        foreach ($pathFieldCandidates as $candidate) {
            if (in_array($candidate, $fields, true)) {
                $pathField = $candidate;
                break;
            }
        }

        if ($pathField === null) {
            return [];
        }

        $rows = $db->table($tableName)
            ->select($pathField)
            ->where($listingField, $listingId)
            ->get()
            ->getResultArray();

        return array_values(array_filter(array_map(
            static fn(array $row): string => trim((string) ($row[$pathField] ?? '')),
            $rows
        )));
    }

    private function deleteRowsByListingId(string $tableName, int $listingId): void
    {
        $db = Database::connect();
        if (! $db->tableExists($tableName)) {
            return;
        }

        $fields = $db->getFieldNames($tableName);
        $listingField = in_array('listing_id', $fields, true)
            ? 'listing_id'
            : (in_array('land_listing_id', $fields, true) ? 'land_listing_id' : null);

        if ($listingField === null) {
            return;
        }

        $db->table($tableName)->where($listingField, $listingId)->delete();
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