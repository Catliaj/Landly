<?php

namespace App\Controllers;

use App\Models\LandListings;
use Config\Database;

class Home extends BaseController
{
    public function index(): string
    {
        return view('LandingPage', [
            'availableListings' => $this->getRandomAvailableListings(6),
        ]);
    }

    public function auth(): string
    {
        return view('AuthPage');
    }

    private function getRandomAvailableListings(int $limit = 6): array
    {
        $limit = max(1, $limit);
        $listingModel = new LandListings();

        $rows = $listingModel
            ->select('land_listings.listing_id, land_listings.title, land_listings.barangay, land_listings.city, land_listings.province, land_listings.price, land_listings.developing_area, land_listings.property_type, land_listings.listing_status, listing_locations.latitude AS listing_latitude, listing_locations.longitude AS listing_longitude')
            ->join('listing_locations', 'listing_locations.listing_id = land_listings.listing_id', 'left')
            ->groupStart()
                ->where('land_listings.listing_status', 'available')
                ->orWhere('land_listings.listing_status', 'Available')
            ->groupEnd()
            ->orderBy('RAND()')
            ->limit(60)
            ->findAll();

        if ($rows === []) {
            return [];
        }

        $rows = array_values(array_filter($rows, fn(array $row): bool => $this->isNasugbuListing($row)));
        if ($rows === []) {
            return [];
        }

        if (count($rows) > $limit) {
            shuffle($rows);
            $rows = array_slice($rows, 0, $limit);
        }

        $listingIds = array_values(array_filter(array_map(
            static fn(array $row): int => (int) ($row['listing_id'] ?? 0),
            $rows
        )));
        $primaryImages = $this->getPrimaryImagesByListing($listingIds);

        $listings = [];
        foreach ($rows as $row) {
            $listingId = (int) ($row['listing_id'] ?? 0);
            if ($listingId <= 0) {
                continue;
            }

            $title = trim((string) ($row['title'] ?? 'Untitled Listing'));
            $listings[] = [
                'listing_id' => $listingId,
                'title' => $title,
                'location_label' => $this->formatLocation($row),
                'price_value' => (float) ($row['price'] ?? 0),
                'area_value' => (float) ($row['developing_area'] ?? 0),
                'property_type_label' => $this->formatPropertyType((string) ($row['property_type'] ?? '')),
                'listing_status' => 'Available',
                'latitude' => $this->normalizeCoordinateValue($row['listing_latitude'] ?? null),
                'longitude' => $this->normalizeCoordinateValue($row['listing_longitude'] ?? null),
                'image_url' => $this->resolveListingImageUrl($primaryImages[$listingId] ?? null, $title),
            ];
        }

        return $listings;
    }

    private function getPrimaryImagesByListing(array $listingIds): array
    {
        if ($listingIds === []) {
            return [];
        }

        $db = Database::connect();
        if (! $db->tableExists('listing_images')) {
            return [];
        }

        $rows = $db->table('listing_images')
            ->select('listing_id, image_path, is_primary, image_id')
            ->whereIn('listing_id', $listingIds)
            ->orderBy('is_primary', 'DESC')
            ->orderBy('image_id', 'ASC')
            ->get()
            ->getResultArray();

        $images = [];
        foreach ($rows as $row) {
            $listingId = (int) ($row['listing_id'] ?? 0);
            if ($listingId <= 0 || isset($images[$listingId])) {
                continue;
            }

            $images[$listingId] = (string) ($row['image_path'] ?? '');
        }

        return $images;
    }

    private function formatLocation(array $listing): string
    {
        $parts = array_filter([
            trim((string) ($listing['barangay'] ?? '')),
            trim((string) ($listing['city'] ?? '')),
            trim((string) ($listing['province'] ?? '')),
        ]);

        return $parts !== [] ? implode(', ', $parts) : 'Location unavailable';
    }

    private function normalizeCoordinateValue(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function isNasugbuListing(array $listing): bool
    {
        $normalizedCity = strtolower(trim((string) ($listing['city'] ?? '')));
        $normalizedLocation = strtolower(trim($this->formatLocation($listing)));

        if (str_contains($normalizedCity, 'nasugbu') || str_contains($normalizedCity, 'nasugbo')) {
            return true;
        }

        return str_contains($normalizedLocation, 'nasugbu') || str_contains($normalizedLocation, 'nasugbo');
    }

    private function formatPropertyType(string $propertyType): string
    {
        return match ($propertyType) {
            'agricultural_land' => 'Agricultural',
            'commercial_land' => 'Commercial',
            'residential_land' => 'Residential',
            default => 'Land',
        };
    }

    private function resolveListingImageUrl(?string $imagePath, string $title): string
    {
        $imagePath = trim((string) $imagePath);

        if ($imagePath !== '') {
            if (preg_match('#^(?:https?:)?//#i', $imagePath) === 1 || str_starts_with($imagePath, 'data:')) {
                return $imagePath;
            }

            return base_url(ltrim(str_replace('\\', '/', $imagePath), '/'));
        }

        $label = trim($title) !== '' ? trim($title) : 'Landly Listing';
        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="480" viewBox="0 0 800 480"><rect width="800" height="480" fill="#183127"/><rect x="36" y="36" width="728" height="408" rx="30" fill="#234236"/><text x="50%%" y="50%%" text-anchor="middle" dominant-baseline="middle" fill="#d2b48c" font-family="Arial, sans-serif" font-size="34">%s</text></svg>',
            htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
        );

        return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
    }
}
