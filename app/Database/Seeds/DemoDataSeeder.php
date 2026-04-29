<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    private string $now;

    public function run()
    {
        $this->now = date('Y-m-d H:i:s');

        $this->db->disableForeignKeyChecks();
        $this->clearExistingDemoData();
        $this->db->enableForeignKeyChecks();

        $users = $this->seedUsers();
        $this->seedSellerProfiles($users['sellers'], $users['admin']);
        $this->seedSellerVerificationDocuments($users['sellers'], $users['admin']);

        $listings = $this->seedListings($users['sellers']);
        $this->seedListingLocations($listings);
        $this->seedListingImages($listings);
        $this->seedListingDocuments($listings);

        $pairs = $this->buildBuyerListingPairs($users['buyers'], $listings, 120);
        $inquiries = $this->seedInquiries($pairs);
        $sessions = $this->seedMessageSessions($inquiries);
        $messages = $this->seedMessages($sessions);

        $this->seedReservations($pairs);
        $this->seedFavorites($pairs);
        $this->seedReviews($pairs);
        $this->seedReports($pairs, $messages, $users['admin']);
        $this->seedDailyViews($users['buyers'], $listings);
        $this->seedListingAnalytics($listings);
        $this->seedNotifications($users, $inquiries, $messages);
    }

    private function clearExistingDemoData(): void
    {
        $demoUsers = $this->db->table('users')
            ->select('user_id')
            ->like('email', 'landly.demo.', 'after')
            ->get()
            ->getResultArray();

        $userIds = array_map(static fn(array $row): int => (int) $row['user_id'], $demoUsers);

        $demoListings = [];
        if ($userIds !== [] && $this->db->tableExists('land_listings')) {
            $demoListings = $this->db->table('land_listings')
                ->select('listing_id')
                ->whereIn('seller_id', $userIds)
                ->get()
                ->getResultArray();
        }

        $listingIds = array_map(static fn(array $row): int => (int) $row['listing_id'], $demoListings);

        $this->deleteWhereIn('reports', 'reporter_user_id', $userIds);
        $this->deleteWhereIn('reports', 'reported_user_id', $userIds);
        $this->deleteWhereIn('notifications', 'user_id', $userIds);
        $this->deleteWhereIn('messages', 'sender_id', $userIds);
        $this->deleteWhereIn('message_sessions', 'buyer_id', $userIds);
        $this->deleteWhereIn('message_sessions', 'seller_id', $userIds);
        $this->deleteWhereIn('reviews', 'reviewer_id', $userIds);
        $this->deleteWhereIn('reviews', 'seller_id', $userIds);
        $this->deleteWhereIn('reservations', 'buyer_id', $userIds);
        $this->deleteWhereIn('reservations', 'seller_id', $userIds);
        $this->deleteWhereIn('buyer_favorites', 'buyer_id', $userIds);
        $this->deleteWhereIn('listing_daily_views', 'viewer_user_id', $userIds);
        $this->deleteWhereIn('inquiries', 'buyer_id', $userIds);
        $this->deleteWhereIn('inquiries', 'seller_id', $userIds);
        $this->deleteWhereIn('seller_verification_documents', 'seller_id', $userIds);
        $this->deleteWhereIn('seller_profiles', 'seller_id', $userIds);

        $this->deleteWhereIn('listing_documents', 'listing_id', $listingIds);
        $this->deleteWhereIn('listing_images', 'listing_id', $listingIds);
        $this->deleteWhereIn('listing_locations', 'listing_id', $listingIds);
        $this->deleteWhereIn('listing_analytics', 'listing_id', $listingIds);
        $this->deleteWhereIn('land_listings', 'listing_id', $listingIds);
        $this->deleteWhereIn('users', 'user_id', $userIds);
    }

    private function seedUsers(): array
    {
        $rows = [];
        $password = password_hash('Password123!', PASSWORD_DEFAULT);

        $rows[] = [
            'first_name' => 'Landly',
            'last_name' => 'Admin',
            'email' => 'landly.demo.admin@example.com',
            'password' => $password,
            'profile_picture' => null,
            'is_active' => 1,
            'roles' => 'admin',
            'created_at' => $this->now,
            'updated_at' => $this->now,
            'last_login' => $this->now,
        ];

        for ($i = 1; $i <= 24; $i++) {
            $rows[] = [
                'first_name' => $this->firstNames()[$i % count($this->firstNames())],
                'last_name' => $this->lastNames()[$i % count($this->lastNames())],
                'email' => sprintf('landly.demo.buyer%02d@example.com', $i),
                'password' => $password,
                'profile_picture' => null,
                'is_active' => 1,
                'roles' => 'buyer',
                'created_at' => $this->daysAgo(80 - $i),
                'updated_at' => $this->now,
                'last_login' => $this->daysAgo($i % 12),
            ];
        }

        for ($i = 1; $i <= 25; $i++) {
            $rows[] = [
                'first_name' => $this->firstNames()[($i + 8) % count($this->firstNames())],
                'last_name' => $this->lastNames()[($i + 5) % count($this->lastNames())],
                'email' => sprintf('landly.demo.seller%02d@example.com', $i),
                'password' => $password,
                'profile_picture' => null,
                'is_active' => 1,
                'roles' => 'seller',
                'created_at' => $this->daysAgo(75 - $i),
                'updated_at' => $this->now,
                'last_login' => $this->daysAgo($i % 15),
            ];
        }

        $this->insertBatchFiltered('users', $rows);

        $users = $this->db->table('users')
            ->select('user_id, roles, email')
            ->like('email', 'landly.demo.', 'after')
            ->orderBy('user_id', 'ASC')
            ->get()
            ->getResultArray();

        $result = ['admin' => null, 'buyers' => [], 'sellers' => []];
        foreach ($users as $user) {
            if ($user['roles'] === 'admin') {
                $result['admin'] = (int) $user['user_id'];
            } elseif ($user['roles'] === 'buyer') {
                $result['buyers'][] = (int) $user['user_id'];
            } elseif ($user['roles'] === 'seller') {
                $result['sellers'][] = (int) $user['user_id'];
            }
        }

        return $result;
    }

    private function seedSellerProfiles(array $sellerIds, ?int $adminId): void
    {
        if (! $this->db->tableExists('seller_profiles')) {
            return;
        }

        $rows = [];
        foreach ($sellerIds as $index => $sellerId) {
            $isVerified = $index % 5 !== 0;
            $rows[] = [
                'seller_id' => $sellerId,
                'bio' => sprintf('Demo seller profile %02d for Landly testing.', $index + 1),
                'achievements' => 'Verified property assistance, document coordination, and buyer walkthroughs.',
                'total_listings' => 2,
                'total_closed_sales' => $index % 4,
                'rating' => 3.75 + (($index % 5) * 0.2),
                'is_verified_seller' => $isVerified ? 1 : 0,
                'verification_status' => $isVerified ? 'verified' : 'pending',
                'verified_at' => $isVerified ? $this->daysAgo(50 - ($index % 25)) : null,
                'verified_by' => $isVerified ? $adminId : null,
            ];
        }

        $this->insertBatchFiltered('seller_profiles', $rows);
    }

    private function seedSellerVerificationDocuments(array $sellerIds, ?int $adminId): void
    {
        if (! $this->db->tableExists('seller_verification_documents')) {
            return;
        }

        $types = ['valid_id', 'title_copy', 'tax_declaration', 'proof_of_ownership'];
        $rows = [];

        foreach ($sellerIds as $index => $sellerId) {
            foreach ($types as $docIndex => $type) {
                $verified = ($index + $docIndex) % 4 !== 0;
                $rows[] = [
                    'seller_id' => $sellerId,
                    'document_type' => $type,
                    'file_path' => sprintf('demo/seller-documents/seller-%d-%s.pdf', $sellerId, $type),
                    'is_verified' => $verified ? 1 : 0,
                    'reviewed_by' => $verified ? $adminId : null,
                    'reviewed_at' => $verified ? $this->daysAgo(34 - ($index % 20)) : null,
                    'uploaded_at' => $this->daysAgo(48 - ($index % 25)),
                ];
            }
        }

        $this->insertBatchFiltered('seller_verification_documents', $rows);
    }

    private function seedListings(array $sellerIds): array
    {
        $barangays = [
            'Aga',
            'Balaytigui',
            'Banilad',
            'Bilaran',
            'Bucana',
            'Bunducan',
            'Butucan',
            'Calayo',
            'Catandaan',
            'Kaylaway',
            'Latag',
            'Looc',
            'Lumbangan',
            'Malapad na Bato',
            'Mataas na Pulo',
            'Maugat',
            'Munting Indang',
            'Natipuan',
            'Pantalan',
            'Papaya',
            'Putat',
            'Reparo',
            'Tumalim',
            'Utod',
            'Wawa',
        ];
        $types = ['residential_land', 'agricultural_land', 'commercial_land'];
        $roads = ['cemented', 'right_of_way', 'none'];
        $views = ['none', 'mountain_view', 'sea_view'];
        $docs = ['complete', 'partial', 'pending'];
        $statuses = ['available', 'in_inquiry', 'reserved', 'closed'];
        $verified = ['true', 'pending', 'false', 'rejected'];
        $rows = [];

        for ($i = 1; $i <= 50; $i++) {
            $rows[] = [
                'seller_id' => $sellerIds[($i - 1) % count($sellerIds)],
                'title' => sprintf('%s Lot %02d', ['Prime', 'Farm', 'Residential', 'Commercial', 'Overlooking'][$i % 5], $i),
                'description' => 'Demo listing for testing Landly dashboards, browsing, inquiries, reports, and analytics.',
                'barangay' => $barangays[$i % count($barangays)],
                'city' => 'Nasugbu',
                'province' => 'Batangas',
                'road_access_type' => $roads[$i % count($roads)],
                'view_type' => $views[$i % count($views)],
                'property_type' => $types[$i % count($types)],
                'is_titled' => $i % 3 !== 0 ? 1 : 0,
                'has_tax_declaration' => 1,
                'has_lra_approved_plan' => $i % 4 === 0 ? 1 : 0,
                'mother_titled_disclosed' => $i % 2 === 0 ? 1 : 0,
                'document_status' => $docs[$i % count($docs)],
                'investment_ready' => $i % 2 === 0 ? 1 : 0,
                'developing_area' => $i % 3 === 0 ? 1 : 0,
                'listing_status' => $statuses[$i % count($statuses)],
                'is_verified_listing' => $verified[$i % count($verified)],
                'price' => 850000 + ($i * 125000),
                'created_at' => $this->daysAgo(55 - ($i % 40)),
                'updated_at' => $this->now,
            ];
        }

        $this->insertBatchFiltered('land_listings', $rows);

        $result = $this->db->table('land_listings')
            ->select('listing_id, seller_id')
            ->whereIn('seller_id', $sellerIds)
            ->orderBy('listing_id', 'ASC')
            ->get()
            ->getResultArray();

        return array_map(static fn(array $row): array => [
            'listing_id' => (int) $row['listing_id'],
            'seller_id' => (int) $row['seller_id'],
        ], $result);
    }

    private function seedListingLocations(array $listings): void
    {
        $rows = [];
        $nasugbuCoordinates = [
            [14.06690000, 120.63040000],
            [14.07460000, 120.63690000],
            [14.08620000, 120.64850000],
            [14.06130000, 120.67620000],
            [14.04470000, 120.68910000],
            [14.02520000, 120.70380000],
            [14.00280000, 120.69650000],
            [13.98740000, 120.67310000],
            [13.97360000, 120.65180000],
            [13.95790000, 120.62230000],
            [13.94410000, 120.60570000],
            [13.93050000, 120.62740000],
            [13.91530000, 120.65090000],
            [13.90260000, 120.68150000],
            [13.89140000, 120.70680000],
            [13.87820000, 120.72610000],
            [13.86050000, 120.74270000],
            [13.84210000, 120.71760000],
            [13.82980000, 120.69240000],
            [13.81490000, 120.66620000],
        ];

        foreach ($listings as $index => $listing) {
            [$latitude, $longitude] = $nasugbuCoordinates[$index % count($nasugbuCoordinates)];
            $offset = intdiv($index, count($nasugbuCoordinates)) * 0.0012;

            $rows[] = [
                'listing_id' => $listing['listing_id'],
                'latitude' => $latitude + $offset,
                'longitude' => $longitude + $offset,
            ];
        }

        $this->insertBatchFiltered('listing_locations', $rows);
    }

    private function seedListingImages(array $listings): void
    {
        $rows = [];
        foreach ($listings as $index => $listing) {
            $rows[] = [
                'listing_id' => $listing['listing_id'],
                'image_path' => 'default1.png',
                'is_primary' => 1,
                'uploaded_at' => $this->daysAgo(45 - ($index % 20)),
            ];
            $rows[] = [
                'listing_id' => $listing['listing_id'],
                'image_path' => 'default1.png',
                'is_primary' => 0,
                'uploaded_at' => $this->daysAgo(44 - ($index % 20)),
            ];
        }

        $this->insertBatchFiltered('listing_images', $rows);
    }

    private function seedListingDocuments(array $listings): void
    {
        $types = ['title', 'tax_declaration', 'lra_plan', 'other'];
        $rows = [];
        foreach ($listings as $index => $listing) {
            for ($i = 0; $i < 2; $i++) {
                $rows[] = [
                    'listing_id' => $listing['listing_id'],
                    'document_type' => $types[($index + $i) % count($types)],
                    'file_path' => sprintf('demo/documents/listing-%d-%d.pdf', $listing['listing_id'], $i + 1),
                    'is_verified' => ($index + $i) % 3 === 0 ? 1 : 0,
                    'uploaded_at' => $this->daysAgo(42 - ($index % 18)),
                ];
            }
        }

        $this->insertBatchFiltered('listing_documents', $rows);
    }

    private function buildBuyerListingPairs(array $buyerIds, array $listings, int $count): array
    {
        $pairs = [];
        $seen = [];
        $i = 0;

        while (count($pairs) < $count) {
            $buyerId = $buyerIds[$i % count($buyerIds)];
            $listing = $listings[($i * 7) % count($listings)];
            $key = $buyerId . ':' . $listing['listing_id'];

            if (! isset($seen[$key]) && $buyerId !== $listing['seller_id']) {
                $seen[$key] = true;
                $pairs[] = [
                    'buyer_id' => $buyerId,
                    'seller_id' => $listing['seller_id'],
                    'listing_id' => $listing['listing_id'],
                ];
            }

            $i++;
        }

        return $pairs;
    }

    private function seedInquiries(array $pairs): array
    {
        $statuses = ['pending', 'accepted', 'rejected', 'reserved', 'closed'];
        $rows = [];
        foreach (array_slice($pairs, 0, 100) as $index => $pair) {
            $rows[] = [
                'listing_id' => $pair['listing_id'],
                'buyer_id' => $pair['buyer_id'],
                'seller_id' => $pair['seller_id'],
                'inquiry_status' => $statuses[$index % count($statuses)],
                'created_at' => $this->daysAgo(35 - ($index % 30)),
                'updated_at' => $this->daysAgo(30 - ($index % 25)),
            ];
        }

        $this->insertBatchFiltered('inquiries', $rows);

        return $this->db->table('inquiries')
            ->select('inquiry_id, listing_id, buyer_id, seller_id, inquiry_status')
            ->whereIn('buyer_id', array_values(array_unique(array_column($pairs, 'buyer_id'))))
            ->orderBy('inquiry_id', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function seedMessageSessions(array $inquiries): array
    {
        $statuses = ['active', 'reserved', 'closed', 'cancelled'];
        $rows = [];
        foreach (array_slice($inquiries, 0, 100) as $index => $inquiry) {
            $rows[] = [
                'listing_id' => (int) $inquiry['listing_id'],
                'inquiry_id' => (int) $inquiry['inquiry_id'],
                'buyer_id' => (int) $inquiry['buyer_id'],
                'seller_id' => (int) $inquiry['seller_id'],
                'session_status' => $statuses[$index % count($statuses)],
                'last_message_at' => $this->daysAgo(15 - ($index % 12)),
                'started_at' => $this->daysAgo(35 - ($index % 30)),
            ];
        }

        $this->insertBatchFiltered('message_sessions', $rows);

        return $this->db->table('message_sessions')
            ->select('session_id, inquiry_id, listing_id, buyer_id, seller_id')
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
                'message_text' => 'Hello, I am interested in this land listing. Is it still available?',
                'attachment_path' => null,
                'is_auto_reply' => 0,
                'is_read' => 1,
                'sent_at' => $this->daysAgo(20 - ($index % 15)),
            ];
            $rows[] = [
                'session_id' => (int) $session['session_id'],
                'sender_id' => (int) $session['seller_id'],
                'message_text' => 'Yes, it is available. I can share more details and schedule a viewing.',
                'attachment_path' => null,
                'is_auto_reply' => 0,
                'is_read' => $index % 4 === 0 ? 0 : 1,
                'sent_at' => $this->daysAgo(19 - ($index % 15)),
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
        $statuses = ['pending', 'confirmed', 'cancelled', 'completed'];
        $rows = [];
        foreach (array_slice($pairs, 0, 100) as $index => $pair) {
            $rows[] = [
                'listing_id' => $pair['listing_id'],
                'buyer_id' => $pair['buyer_id'],
                'seller_id' => $pair['seller_id'],
                'reservation_status' => $statuses[$index % count($statuses)],
                'reservation_date' => $this->daysAgo(18 - ($index % 20)),
                'notes' => 'Demo reservation record for testing transaction reports.',
            ];
        }

        $this->insertBatchFiltered('reservations', $rows);
    }

    private function seedFavorites(array $pairs): void
    {
        $rows = [];
        foreach (array_slice($pairs, 0, 100) as $index => $pair) {
            $rows[] = [
                'buyer_id' => $pair['buyer_id'],
                'listing_id' => $pair['listing_id'],
                'created_at' => $this->daysAgo(28 - ($index % 24)),
                'updated_at' => $this->now,
            ];
        }

        $this->insertBatchFiltered('buyer_favorites', $rows);
    }

    private function seedReviews(array $pairs): void
    {
        $rows = [];
        foreach (array_slice($pairs, 0, 100) as $index => $pair) {
            $rows[] = [
                'reviewer_id' => $pair['buyer_id'],
                'seller_id' => $pair['seller_id'],
                'listing_id' => $pair['listing_id'],
                'rating' => 3 + ($index % 3),
                'comment' => 'Demo review for checking seller reputation and listing feedback.',
                'created_at' => $this->daysAgo(12 - ($index % 10)),
            ];
        }

        $this->insertBatchFiltered('reviews', $rows);
    }

    private function seedReports(array $pairs, array $messages, ?int $adminId): void
    {
        if (! $this->db->tableExists('reports')) {
            return;
        }

        $fields = $this->db->getFieldNames('reports');
        $newReportsTable = in_array('reporter_user_id', $fields, true);
        $rows = [];
        $reasons = ['misleading_photos', 'incorrect_location', 'spam', 'inappropriate_message', 'other'];

        foreach (array_slice($pairs, 0, 100) as $index => $pair) {
            $message = $messages[$index % max(1, count($messages))] ?? null;
            $isMessageReport = $index % 2 === 1 && $message !== null;
            $status = ['pending', 'reviewed', 'dismissed', 'action_taken'][$index % 4];

            if ($newReportsTable) {
                $rows[] = [
                    'report_type' => $isMessageReport ? 'message' : 'listing',
                    'reporter_user_id' => $pair['buyer_id'],
                    'reported_user_id' => $pair['seller_id'],
                    'listing_id' => $pair['listing_id'],
                    'message_id' => $isMessageReport ? (int) $message['message_id'] : null,
                    'session_id' => $isMessageReport ? (int) $message['session_id'] : null,
                    'inquiry_id' => null,
                    'reason' => $reasons[$index % count($reasons)],
                    'other_reason' => $index % 5 === 4 ? 'Demo custom report reason' : null,
                    'description' => 'Demo report record for admin moderation testing.',
                    'evidence_path' => null,
                    'status' => $status,
                    'admin_notes' => $status === 'pending' ? null : 'Reviewed demo report.',
                    'reviewed_by' => $status === 'pending' ? null : $adminId,
                    'reviewed_at' => $status === 'pending' ? null : $this->daysAgo(4 - ($index % 3)),
                    'created_at' => $this->daysAgo(10 - ($index % 8)),
                    'updated_at' => $this->now,
                ];
            } else {
                $rows[] = [
                    'reported_by' => $pair['buyer_id'],
                    'reported_against' => $pair['seller_id'],
                    'subject' => $isMessageReport ? 'Message Report' : 'Listing Report',
                    'listing_id' => $pair['listing_id'],
                    'reason' => $reasons[$index % count($reasons)],
                    'description' => 'Demo report record for admin moderation testing.',
                    'evidence_path' => null,
                    'status' => $status === 'dismissed' ? 'dismissed' : ($status === 'pending' ? 'pending' : 'resolved'),
                    'admin_notes' => $status === 'pending' ? null : 'Reviewed demo report.',
                    'created_at' => $this->daysAgo(10 - ($index % 8)),
                    'updated_at' => $this->now,
                    'resolved_at' => $status === 'pending' ? null : $this->daysAgo(4 - ($index % 3)),
                ];
            }
        }

        $this->insertBatchFiltered('reports', $rows);
    }

    private function seedDailyViews(array $buyerIds, array $listings): void
    {
        $rows = [];
        $seen = [];
        $i = 0;

        while (count($rows) < 100) {
            $listing = $listings[($i * 5) % count($listings)];
            $buyerId = $buyerIds[$i % count($buyerIds)];
            $date = date('Y-m-d', strtotime('-' . ($i % 30) . ' days'));
            $key = $listing['listing_id'] . ':' . $buyerId . ':' . $date;

            if (! isset($seen[$key])) {
                $seen[$key] = true;
                $rows[] = [
                    'listing_id' => $listing['listing_id'],
                    'viewer_user_id' => $buyerId,
                    'view_date' => $date,
                    'created_at' => $date . ' 09:00:00',
                ];
            }

            $i++;
        }

        $this->insertBatchFiltered('listing_daily_views', $rows);
    }

    private function seedListingAnalytics(array $listings): void
    {
        $rows = [];
        foreach ($listings as $index => $listing) {
            $rows[] = [
                'listing_id' => $listing['listing_id'],
                'total_views' => 20 + ($index * 3),
                'total_inquiries' => 1 + ($index % 8),
                'total_reservations' => $index % 5,
                'total_closed' => $index % 4 === 0 ? 1 : 0,
                'last_viewed_at' => $this->daysAgo($index % 14),
            ];
        }

        $this->insertBatchFiltered('listing_analytics', $rows);
    }

    private function seedNotifications(array $users, array $inquiries, array $messages): void
    {
        $allUsers = array_values(array_filter(array_merge([$users['admin']], $users['buyers'], $users['sellers'])));
        $types = ['message_received', 'listing_status_changed', 'inquiry_status_changed', 'message_read_state_changed'];
        $rows = [];

        for ($i = 0; $i < 100; $i++) {
            $inquiry = $inquiries[$i % count($inquiries)];
            $message = $messages[$i % count($messages)] ?? null;
            $rows[] = [
                'user_id' => $allUsers[$i % count($allUsers)],
                'notification_type' => $types[$i % count($types)],
                'notification_status' => $i % 9 === 0 ? 'archived' : 'active',
                'listing_id' => (int) $inquiry['listing_id'],
                'inquiry_id' => (int) $inquiry['inquiry_id'],
                'message_id' => $message ? (int) $message['message_id'] : null,
                'message' => 'Demo notification for dashboard testing.',
                'is_read' => $i % 3 === 0 ? 1 : 0,
                'created_at' => $this->daysAgo(9 - ($i % 7)),
                'updated_at' => $this->now,
            ];
        }

        $this->insertBatchFiltered('notifications', $rows);
    }

    private function insertBatchFiltered(string $table, array $rows): void
    {
        if ($rows === [] || ! $this->db->tableExists($table)) {
            return;
        }

        $fields = array_flip($this->db->getFieldNames($table));
        $filtered = array_map(static function (array $row) use ($fields): array {
            return array_intersect_key($row, $fields);
        }, $rows);

        $this->db->table($table)->insertBatch($filtered);
    }

    private function deleteWhereIn(string $table, string $column, array $values): void
    {
        if ($values === [] || ! $this->db->tableExists($table)) {
            return;
        }

        $fields = $this->db->getFieldNames($table);
        if (! in_array($column, $fields, true)) {
            return;
        }

        $this->db->table($table)->whereIn($column, $values)->delete();
    }

    private function daysAgo(int $days): string
    {
        return date('Y-m-d H:i:s', strtotime('-' . max(0, $days) . ' days'));
    }

    private function firstNames(): array
    {
        return ['Miguel', 'Sofia', 'Carlo', 'Andrea', 'Rafael', 'Bianca', 'Paolo', 'Isabel', 'Marco', 'Camille', 'Nico', 'Patricia'];
    }

    private function lastNames(): array
    {
        return ['Santos', 'Reyes', 'Cruz', 'Garcia', 'Mendoza', 'Torres', 'Ramos', 'Flores', 'Aquino', 'Castillo', 'Dela Cruz', 'Villanueva'];
    }
}
