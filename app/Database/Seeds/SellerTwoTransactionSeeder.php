<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SellerTwoTransactionSeeder extends Seeder
{
    private const SELLER_ID = 2;
    private const LISTING_PREFIX = 'Seller 2 Demo Land';
    private string $now;
    private bool $supportsSoldStatus = false;

    public function run()
    {
        $this->now = date('Y-m-d H:i:s');
        $this->supportsSoldStatus = $this->enumAllows('land_listings', 'listing_status', 'sold');

        $this->db->disableForeignKeyChecks();
        $this->clearPreviousSellerTwoDemoData();
        $this->db->enableForeignKeyChecks();

        $this->ensureSellerTwoExists();
        $buyerIds = $this->ensureDemoBuyers();
        $this->ensureSellerProfile();

        $listings = $this->seedListings();
        $this->seedListingLocations($listings);
        $this->seedListingImages($listings);
        $this->seedListingDocuments($listings);
        $this->seedListingAnalytics($listings);

        $pairs = $this->buildPairs($buyerIds, $listings);
        $inquiries = $this->seedInquiries($pairs);
        $sessions = $this->seedMessageSessions($inquiries);
        $messages = $this->seedMessages($sessions);

        $this->seedReservations($pairs);
        $this->seedFavorites($pairs);
        $this->seedReviews($pairs);
        $this->seedReports($pairs, $messages);
        $this->seedDailyViews($buyerIds, $listings);
        $this->seedNotifications($inquiries, $messages);
    }

    private function clearPreviousSellerTwoDemoData(): void
    {
        $listingRows = $this->db->table('land_listings')
            ->select('listing_id')
            ->where('seller_id', self::SELLER_ID)
            ->like('title', self::LISTING_PREFIX, 'after')
            ->get()
            ->getResultArray();

        $listingIds = array_map(static fn(array $row): int => (int) $row['listing_id'], $listingRows);

        $buyerRows = $this->db->table('users')
            ->select('user_id')
            ->like('email', 'landly.seller2.buyer', 'after')
            ->get()
            ->getResultArray();

        $buyerIds = array_map(static fn(array $row): int => (int) $row['user_id'], $buyerRows);

        $sessionRows = [];
        if ($listingIds !== [] && $this->db->tableExists('message_sessions')) {
            $sessionRows = $this->db->table('message_sessions')
                ->select('session_id')
                ->whereIn('listing_id', $listingIds)
                ->get()
                ->getResultArray();
        }

        $sessionIds = array_map(static fn(array $row): int => (int) $row['session_id'], $sessionRows);

        $this->deleteWhereIn('reports', 'listing_id', $listingIds);
        $this->deleteWhereIn('notifications', 'listing_id', $listingIds);
        $this->deleteWhereIn('messages', 'session_id', $sessionIds);
        $this->deleteWhereIn('message_sessions', 'listing_id', $listingIds);
        $this->deleteWhereIn('reviews', 'listing_id', $listingIds);
        $this->deleteWhereIn('reservations', 'listing_id', $listingIds);
        $this->deleteWhereIn('buyer_favorites', 'listing_id', $listingIds);
        $this->deleteWhereIn('listing_daily_views', 'listing_id', $listingIds);
        $this->deleteWhereIn('inquiries', 'listing_id', $listingIds);
        $this->deleteWhereIn('listing_documents', 'listing_id', $listingIds);
        $this->deleteWhereIn('listing_images', 'listing_id', $listingIds);
        $this->deleteWhereIn('listing_locations', 'listing_id', $listingIds);
        $this->deleteWhereIn('listing_analytics', 'listing_id', $listingIds);
        $this->deleteWhereIn('land_listings', 'listing_id', $listingIds);
        $this->deleteWhereIn('users', 'user_id', $buyerIds);
    }

    private function ensureSellerTwoExists(): void
    {
        $row = $this->db->table('users')->where('user_id', self::SELLER_ID)->get()->getRowArray();
        $data = [
            'first_name' => 'Seller',
            'last_name' => 'Two',
            'email' => 'seller2@landly.demo',
            'password' => password_hash('Password123!', PASSWORD_DEFAULT),
            'profile_picture' => null,
            'is_active' => 1,
            'roles' => 'seller',
            'updated_at' => $this->now,
            'last_login' => $this->now,
        ];

        if ($row) {
            $this->db->table('users')->where('user_id', self::SELLER_ID)->update(array_intersect_key($data, $this->fields('users')));
            return;
        }

        $data['user_id'] = self::SELLER_ID;
        $data['created_at'] = $this->daysAgo(30);
        $this->db->table('users')->insert(array_intersect_key($data, $this->fields('users')));
    }

    private function ensureDemoBuyers(): array
    {
        $password = password_hash('Password123!', PASSWORD_DEFAULT);
        $rows = [];

        for ($i = 1; $i <= 25; $i++) {
            $rows[] = [
                'first_name' => 'Buyer',
                'last_name' => sprintf('Demo %02d', $i),
                'email' => sprintf('landly.seller2.buyer%02d@example.com', $i),
                'password' => $password,
                'profile_picture' => null,
                'is_active' => 1,
                'roles' => 'buyer',
                'created_at' => $this->daysAgo(30 - ($i % 20)),
                'updated_at' => $this->now,
                'last_login' => $this->daysAgo($i % 7),
            ];
        }

        $this->insertBatchFiltered('users', $rows);

        $buyerRows = $this->db->table('users')
            ->select('user_id')
            ->like('email', 'landly.seller2.buyer', 'after')
            ->orderBy('user_id', 'ASC')
            ->get()
            ->getResultArray();

        return array_map(static fn(array $row): int => (int) $row['user_id'], $buyerRows);
    }

    private function ensureSellerProfile(): void
    {
        if (! $this->db->tableExists('seller_profiles')) {
            return;
        }

        $this->db->table('seller_profiles')->where('seller_id', self::SELLER_ID)->delete();
        $this->insertBatchFiltered('seller_profiles', [[
            'seller_id' => self::SELLER_ID,
            'bio' => 'Seeded seller profile with 100 Nasugbu land listings and transaction records.',
            'achievements' => 'Demo data for seller transaction testing.',
            'total_listings' => 100,
            'total_closed_sales' => 25,
            'rating' => 4.60,
            'is_verified_seller' => 1,
            'verification_status' => 'verified',
            'verified_at' => $this->daysAgo(28),
            'verified_by' => $this->findAdminId(),
        ]]);
    }

    private function seedListings(): array
    {
        $barangays = ['Aga', 'Balaytigui', 'Banilad', 'Bilaran', 'Bucana', 'Bunducan', 'Calayo', 'Kaylaway', 'Latag', 'Looc', 'Lumbangan', 'Natipuan', 'Papaya', 'Putat', 'Tumalim', 'Wawa'];
        $types = ['residential_land', 'agricultural_land', 'commercial_land'];
        $roads = ['cemented', 'right_of_way', 'none'];
        $views = ['none', 'mountain_view', 'sea_view'];
        $documents = ['complete', 'partial', 'pending'];
        $verificationStatuses = ['true', 'pending', 'true', 'rejected', 'false'];
        $listingStatuses = ['available', 'in_inquiry', 'reserved', $this->soldStatus(), 'available'];
        $rows = [];

        for ($i = 1; $i <= 100; $i++) {
            $rows[] = [
                'seller_id' => self::SELLER_ID,
                'title' => sprintf('%s %03d', self::LISTING_PREFIX, $i),
                'description' => 'Seeded Nasugbu, Batangas land listing for seller transaction testing.',
                'barangay' => $barangays[($i - 1) % count($barangays)],
                'city' => 'Nasugbu',
                'province' => 'Batangas',
                'road_access_type' => $roads[$i % count($roads)],
                'view_type' => $views[$i % count($views)],
                'property_type' => $types[$i % count($types)],
                'is_titled' => $i % 4 === 0 ? 0 : 1,
                'has_tax_declaration' => 1,
                'has_lra_approved_plan' => $i % 3 === 0 ? 1 : 0,
                'mother_titled_disclosed' => $i % 2 === 0 ? 1 : 0,
                'document_status' => $documents[$i % count($documents)],
                'investment_ready' => $i % 2 === 0 ? 1 : 0,
                'developing_area' => $i % 3 === 0 ? 1 : 0,
                'listing_status' => $listingStatuses[$i % count($listingStatuses)],
                'is_verified_listing' => $verificationStatuses[$i % count($verificationStatuses)],
                'price' => 900000 + ($i * 75000),
                'created_at' => $this->daysAgo(30 - (($i - 1) % 31)),
                'updated_at' => $this->daysAgo(15 - (($i - 1) % 16)),
            ];
        }

        $this->insertBatchFiltered('land_listings', $rows);

        return $this->db->table('land_listings')
            ->select('listing_id')
            ->where('seller_id', self::SELLER_ID)
            ->like('title', self::LISTING_PREFIX, 'after')
            ->orderBy('listing_id', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function seedListingLocations(array $listings): void
    {
        $coords = [
            [14.06690000, 120.63040000], [14.07460000, 120.63690000], [14.08620000, 120.64850000],
            [14.06130000, 120.67620000], [14.04470000, 120.68910000], [14.02520000, 120.70380000],
            [14.00280000, 120.69650000], [13.98740000, 120.67310000], [13.97360000, 120.65180000],
            [13.95790000, 120.62230000], [13.94410000, 120.60570000], [13.93050000, 120.62740000],
            [13.91530000, 120.65090000], [13.90260000, 120.68150000], [13.89140000, 120.70680000],
            [13.87820000, 120.72610000], [13.86050000, 120.74270000], [13.84210000, 120.71760000],
            [13.82980000, 120.69240000], [13.81490000, 120.66620000],
        ];
        $rows = [];

        foreach ($listings as $index => $listing) {
            [$lat, $lng] = $coords[$index % count($coords)];
            $offset = intdiv($index, count($coords)) * 0.001;
            $rows[] = [
                'listing_id' => (int) $listing['listing_id'],
                'latitude' => $lat + $offset,
                'longitude' => $lng + $offset,
            ];
        }

        $this->insertBatchFiltered('listing_locations', $rows);
    }

    private function seedListingImages(array $listings): void
    {
        $rows = [];
        foreach ($listings as $index => $listing) {
            $rows[] = [
                'listing_id' => (int) $listing['listing_id'],
                'image_path' => 'default1.png',
                'is_primary' => 1,
                'uploaded_at' => $this->daysAgo(30 - ($index % 31)),
            ];
        }
        $this->insertBatchFiltered('listing_images', $rows);
    }

    private function seedListingDocuments(array $listings): void
    {
        $types = ['title', 'tax_declaration', 'lra_plan', 'other'];
        $rows = [];
        foreach ($listings as $index => $listing) {
            $rows[] = [
                'listing_id' => (int) $listing['listing_id'],
                'document_type' => $types[$index % count($types)],
                'file_path' => sprintf('demo/seller-2/listing-%d-proof.pdf', (int) $listing['listing_id']),
                'is_verified' => $index % 4 !== 0 ? 1 : 0,
                'uploaded_at' => $this->daysAgo(29 - ($index % 30)),
            ];
        }
        $this->insertBatchFiltered('listing_documents', $rows);
    }

    private function seedListingAnalytics(array $listings): void
    {
        $rows = [];
        foreach ($listings as $index => $listing) {
            $rows[] = [
                'listing_id' => (int) $listing['listing_id'],
                'total_views' => 10 + ($index * 2),
                'total_inquiries' => 1,
                'total_reservations' => $index % 3 === 0 ? 1 : 0,
                'total_closed' => $index % 4 === 0 ? 1 : 0,
                'last_viewed_at' => $this->daysAgo($index % 30),
            ];
        }
        $this->insertBatchFiltered('listing_analytics', $rows);
    }

    private function buildPairs(array $buyerIds, array $listings): array
    {
        $pairs = [];
        foreach ($listings as $index => $listing) {
            $pairs[] = [
                'listing_id' => (int) $listing['listing_id'],
                'seller_id' => self::SELLER_ID,
                'buyer_id' => $buyerIds[$index % count($buyerIds)],
            ];
        }
        return $pairs;
    }

    private function seedInquiries(array $pairs): array
    {
        $statuses = ['pending', 'accepted', 'reserved', 'closed', 'accepted'];
        $rows = [];
        foreach ($pairs as $index => $pair) {
            $rows[] = [
                'listing_id' => $pair['listing_id'],
                'buyer_id' => $pair['buyer_id'],
                'seller_id' => self::SELLER_ID,
                'inquiry_status' => $statuses[$index % count($statuses)],
                'created_at' => $this->daysAgo(30 - ($index % 31)),
                'updated_at' => $this->daysAgo(14 - ($index % 15)),
            ];
        }
        $this->insertBatchFiltered('inquiries', $rows);

        return $this->db->table('inquiries')
            ->select('inquiry_id, listing_id, buyer_id, seller_id, inquiry_status')
            ->where('seller_id', self::SELLER_ID)
            ->whereIn('listing_id', array_column($pairs, 'listing_id'))
            ->orderBy('inquiry_id', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function seedMessageSessions(array $inquiries): array
    {
        $statuses = ['active', 'active', 'reserved', 'closed'];
        $rows = [];
        foreach ($inquiries as $index => $inquiry) {
            $rows[] = [
                'listing_id' => (int) $inquiry['listing_id'],
                'inquiry_id' => (int) $inquiry['inquiry_id'],
                'buyer_id' => (int) $inquiry['buyer_id'],
                'seller_id' => self::SELLER_ID,
                'session_status' => $statuses[$index % count($statuses)],
                'last_message_at' => $this->daysAgo(10 - ($index % 11)),
                'started_at' => $this->daysAgo(30 - ($index % 31)),
            ];
        }
        $this->insertBatchFiltered('message_sessions', $rows);

        return $this->db->table('message_sessions')
            ->select('session_id, inquiry_id, listing_id, buyer_id, seller_id')
            ->where('seller_id', self::SELLER_ID)
            ->whereIn('inquiry_id', array_column($inquiries, 'inquiry_id'))
            ->orderBy('session_id', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function seedMessages(array $sessions): array
    {
        $rows = [];
        foreach ($sessions as $index => $session) {
            $rows[] = [
                'session_id' => (int) $session['session_id'],
                'sender_id' => (int) $session['buyer_id'],
                'message_text' => 'I am interested in this Nasugbu listing. Is it still available?',
                'attachment_path' => null,
                'is_auto_reply' => 0,
                'is_read' => 1,
                'sent_at' => $this->daysAgo(20 - ($index % 21)),
            ];
            $rows[] = [
                'session_id' => (int) $session['session_id'],
                'sender_id' => self::SELLER_ID,
                'message_text' => 'Yes, this listing is available. I can provide details and viewing schedule.',
                'attachment_path' => null,
                'is_auto_reply' => 0,
                'is_read' => $index % 5 === 0 ? 0 : 1,
                'sent_at' => $this->daysAgo(19 - ($index % 20)),
            ];
        }
        $this->insertBatchFiltered('messages', $rows);

        return $this->db->table('messages')
            ->select('message_id, session_id, sender_id')
            ->whereIn('session_id', array_column($sessions, 'session_id'))
            ->orderBy('message_id', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function seedReservations(array $pairs): void
    {
        $statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        $rows = [];
        foreach ($pairs as $index => $pair) {
            $rows[] = [
                'listing_id' => $pair['listing_id'],
                'buyer_id' => $pair['buyer_id'],
                'seller_id' => self::SELLER_ID,
                'reservation_status' => $statuses[$index % count($statuses)],
                'reservation_date' => $this->daysAgo(18 - ($index % 19)),
                'notes' => 'Seller 2 seeded reservation transaction.',
            ];
        }
        $this->insertBatchFiltered('reservations', $rows);
    }

    private function seedFavorites(array $pairs): void
    {
        $rows = [];
        foreach ($pairs as $index => $pair) {
            $rows[] = [
                'buyer_id' => $pair['buyer_id'],
                'listing_id' => $pair['listing_id'],
                'created_at' => $this->daysAgo(25 - ($index % 26)),
                'updated_at' => $this->now,
            ];
        }
        $this->insertBatchFiltered('buyer_favorites', $rows);
    }

    private function seedReviews(array $pairs): void
    {
        $rows = [];
        foreach ($pairs as $index => $pair) {
            $rows[] = [
                'reviewer_id' => $pair['buyer_id'],
                'seller_id' => self::SELLER_ID,
                'listing_id' => $pair['listing_id'],
                'rating' => 3 + ($index % 3),
                'comment' => 'Seeded review for seller 2 transaction testing.',
                'created_at' => $this->daysAgo(12 - ($index % 13)),
            ];
        }
        $this->insertBatchFiltered('reviews', $rows);
    }

    private function seedReports(array $pairs, array $messages): void
    {
        if (! $this->db->tableExists('reports')) {
            return;
        }

        $newReportsTable = in_array('reporter_user_id', $this->db->getFieldNames('reports'), true);
        $adminId = $this->findAdminId();
        $reasons = ['misleading_photos', 'incorrect_location', 'spam', 'inappropriate_message'];
        $rows = [];

        foreach ($pairs as $index => $pair) {
            $message = $messages[$index % max(1, count($messages))] ?? null;
            if ($newReportsTable) {
                $rows[] = [
                    'report_type' => $index % 2 === 0 ? 'listing' : 'message',
                    'reporter_user_id' => $pair['buyer_id'],
                    'reported_user_id' => self::SELLER_ID,
                    'listing_id' => $pair['listing_id'],
                    'message_id' => $message ? (int) $message['message_id'] : null,
                    'session_id' => $message ? (int) $message['session_id'] : null,
                    'inquiry_id' => null,
                    'reason' => $reasons[$index % count($reasons)],
                    'other_reason' => null,
                    'description' => 'Seeded report for seller 2 moderation testing.',
                    'evidence_path' => null,
                    'status' => ['pending', 'reviewed', 'dismissed', 'action_taken'][$index % 4],
                    'admin_notes' => $index % 4 === 0 ? null : 'Seeded review note.',
                    'reviewed_by' => $index % 4 === 0 ? null : $adminId,
                    'reviewed_at' => $index % 4 === 0 ? null : $this->daysAgo(6 - ($index % 7)),
                    'created_at' => $this->daysAgo(10 - ($index % 11)),
                    'updated_at' => $this->now,
                ];
            } else {
                $rows[] = [
                    'reported_by' => $pair['buyer_id'],
                    'reported_against' => self::SELLER_ID,
                    'subject' => 'Seller 2 Demo Report',
                    'listing_id' => $pair['listing_id'],
                    'reason' => $reasons[$index % count($reasons)],
                    'description' => 'Seeded report for seller 2 moderation testing.',
                    'status' => ['pending', 'resolved', 'dismissed'][$index % 3],
                    'created_at' => $this->daysAgo(10 - ($index % 11)),
                    'updated_at' => $this->now,
                ];
            }
        }
        $this->insertBatchFiltered('reports', $rows);
    }

    private function seedDailyViews(array $buyerIds, array $listings): void
    {
        $rows = [];
        foreach ($listings as $index => $listing) {
            $date = date('Y-m-d', strtotime('-' . ($index % 31) . ' days'));
            $rows[] = [
                'listing_id' => (int) $listing['listing_id'],
                'viewer_user_id' => $buyerIds[$index % count($buyerIds)],
                'view_date' => $date,
                'created_at' => $date . ' 10:00:00',
            ];
        }
        $this->insertBatchFiltered('listing_daily_views', $rows);
    }

    private function seedNotifications(array $inquiries, array $messages): void
    {
        $types = ['message_received', 'listing_status_changed', 'inquiry_status_changed', 'message_read_state_changed'];
        $rows = [];
        foreach ($inquiries as $index => $inquiry) {
            $message = $messages[$index % max(1, count($messages))] ?? null;
            $rows[] = [
                'user_id' => self::SELLER_ID,
                'notification_type' => $types[$index % count($types)],
                'notification_status' => $index % 8 === 0 ? 'archived' : 'active',
                'listing_id' => (int) $inquiry['listing_id'],
                'inquiry_id' => (int) $inquiry['inquiry_id'],
                'message_id' => $message ? (int) $message['message_id'] : null,
                'message' => 'Seeded notification for seller 2.',
                'is_read' => $index % 3 === 0 ? 1 : 0,
                'created_at' => $this->daysAgo(9 - ($index % 10)),
                'updated_at' => $this->now,
            ];
        }
        $this->insertBatchFiltered('notifications', $rows);
    }

    private function soldStatus(): string
    {
        return $this->supportsSoldStatus ? 'sold' : 'closed';
    }

    private function enumAllows(string $table, string $column, string $value): bool
    {
        if (! $this->db->tableExists($table)) {
            return false;
        }

        foreach ($this->db->getFieldData($table) as $field) {
            if (($field->name ?? '') === $column) {
                return str_contains(strtolower((string) ($field->type ?? '')), "'" . strtolower($value) . "'");
            }
        }

        return false;
    }

    private function findAdminId(): ?int
    {
        $row = $this->db->table('users')
            ->select('user_id')
            ->where('roles', 'admin')
            ->orderBy('user_id', 'ASC')
            ->get()
            ->getRowArray();

        return $row ? (int) $row['user_id'] : null;
    }

    private function insertBatchFiltered(string $table, array $rows): void
    {
        if ($rows === [] || ! $this->db->tableExists($table)) {
            return;
        }

        $fields = $this->fields($table);
        $filtered = array_map(static fn(array $row): array => array_intersect_key($row, $fields), $rows);
        $this->db->table($table)->insertBatch($filtered);
    }

    private function deleteWhereIn(string $table, string $column, array $values): void
    {
        $values = array_values(array_unique(array_filter($values)));
        if ($values === [] || ! $this->db->tableExists($table) || ! isset($this->fields($table)[$column])) {
            return;
        }

        $this->db->table($table)->whereIn($column, $values)->delete();
    }

    private function fields(string $table): array
    {
        return array_flip($this->db->getFieldNames($table));
    }

    private function daysAgo(int $days): string
    {
        return date('Y-m-d H:i:s', strtotime('-' . max(0, $days) . ' days'));
    }
}
