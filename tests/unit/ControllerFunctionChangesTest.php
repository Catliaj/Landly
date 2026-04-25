<?php

use App\Controllers\Buyer\ChatbotController;
use App\Controllers\Buyer\DashboardController;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

trait LandlyTestSchemaTrait
{
    protected function resetLandlyTestSchema(): void
    {
        $db = db_connect();
        $usersTable = $db->prefixTable('users');
        $landListingsTable = $db->prefixTable('land_listings');
        $listingAnalyticsTable = $db->prefixTable('listing_analytics');
        $listingDailyViewsTable = $db->prefixTable('listing_daily_views');
        $buyerFavoritesTable = $db->prefixTable('buyer_favorites');
        $inquiriesTable = $db->prefixTable('inquiries');
        $messageSessionsTable = $db->prefixTable('message_sessions');
        $messagesTable = $db->prefixTable('messages');

        foreach ([
            $messagesTable,
            $messageSessionsTable,
            $inquiriesTable,
            $buyerFavoritesTable,
            $listingAnalyticsTable,
            $listingDailyViewsTable,
            $landListingsTable,
            $usersTable,
        ] as $table) {
            $db->query('DROP TABLE IF EXISTS ' . $table);
        }

        $db->query(sprintf(
            'CREATE TABLE %s (user_id INTEGER PRIMARY KEY AUTOINCREMENT, first_name TEXT NOT NULL, last_name TEXT NOT NULL, email TEXT NOT NULL, password TEXT DEFAULT "", profile_picture TEXT DEFAULT "", is_active INTEGER NOT NULL DEFAULT 1, roles TEXT DEFAULT "buyer", created_at TEXT DEFAULT NULL, updated_at TEXT DEFAULT NULL, last_login TEXT DEFAULT NULL)',
            $usersTable
        ));

        $db->query(sprintf(
            'CREATE TABLE %s (listing_id INTEGER PRIMARY KEY AUTOINCREMENT, seller_id INTEGER NOT NULL, title TEXT NOT NULL, description TEXT DEFAULT "", barangay TEXT DEFAULT "", city TEXT DEFAULT "", province TEXT DEFAULT "", price REAL NOT NULL DEFAULT 0, developing_area REAL NOT NULL DEFAULT 0, property_type TEXT DEFAULT "", road_access_type TEXT DEFAULT "", view_type TEXT DEFAULT "", listing_status TEXT DEFAULT "available", is_verified_listing TEXT DEFAULT "true", is_titled INTEGER DEFAULT 0, has_tax_declaration INTEGER DEFAULT 0, has_lra_approved_plan INTEGER DEFAULT 0, mother_titled_disclosed INTEGER DEFAULT 0, investment_ready INTEGER DEFAULT 0, document_status TEXT DEFAULT "pending", created_at TEXT DEFAULT NULL, updated_at TEXT DEFAULT NULL)',
            $landListingsTable
        ));

        $db->query(sprintf(
            'CREATE TABLE %s (analytics_id INTEGER PRIMARY KEY AUTOINCREMENT, listing_id INTEGER NOT NULL UNIQUE, total_views INTEGER NOT NULL DEFAULT 0, total_inquiries INTEGER NOT NULL DEFAULT 0, total_reservations INTEGER NOT NULL DEFAULT 0, total_closed INTEGER NOT NULL DEFAULT 0, last_viewed_at TEXT DEFAULT NULL)',
            $listingAnalyticsTable
        ));

        $db->query(sprintf(
            'CREATE TABLE %s (daily_view_id INTEGER PRIMARY KEY AUTOINCREMENT, listing_id INTEGER NOT NULL, viewer_user_id INTEGER NOT NULL, view_date TEXT NOT NULL, created_at TEXT DEFAULT NULL)',
            $listingDailyViewsTable
        ));
        $db->query(sprintf('CREATE UNIQUE INDEX idx_listing_daily_views_unique ON %s (listing_id, viewer_user_id, view_date)', $listingDailyViewsTable));

        $db->query(sprintf(
            'CREATE TABLE %s (favorite_id INTEGER PRIMARY KEY AUTOINCREMENT, buyer_id INTEGER NOT NULL, listing_id INTEGER NOT NULL)',
            $buyerFavoritesTable
        ));

        $db->query(sprintf(
            'CREATE TABLE %s (inquiry_id INTEGER PRIMARY KEY AUTOINCREMENT, listing_id INTEGER NOT NULL, buyer_id INTEGER NOT NULL, seller_id INTEGER NOT NULL, inquiry_status TEXT NOT NULL DEFAULT "pending", created_at TEXT DEFAULT NULL, updated_at TEXT DEFAULT NULL)',
            $inquiriesTable
        ));

        $db->query(sprintf(
            'CREATE TABLE %s (session_id INTEGER PRIMARY KEY AUTOINCREMENT, listing_id INTEGER NOT NULL, inquiry_id INTEGER NOT NULL, buyer_id INTEGER NOT NULL, seller_id INTEGER NOT NULL, session_status TEXT DEFAULT "active", last_message_at TEXT DEFAULT NULL, started_at TEXT DEFAULT NULL)',
            $messageSessionsTable
        ));

        $db->query(sprintf(
            'CREATE TABLE %s (message_id INTEGER PRIMARY KEY AUTOINCREMENT, session_id INTEGER NOT NULL, sender_id INTEGER NOT NULL, message_text TEXT DEFAULT NULL, attachment_path TEXT DEFAULT NULL, is_auto_reply INTEGER NOT NULL DEFAULT 0, is_read INTEGER NOT NULL DEFAULT 0, sent_at TEXT DEFAULT NULL)',
            $messagesTable
        ));
    }

    protected function seedUser(int $userId, string $firstName, string $lastName, string $email, int $isActive = 1, string $roles = 'buyer'): void
    {
        db_connect()->table('users')->insert([
            'user_id' => $userId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => 'secret',
            'profile_picture' => '',
            'is_active' => $isActive,
            'roles' => $roles,
            'created_at' => '2026-04-25 10:00:00',
            'updated_at' => '2026-04-25 10:00:00',
        ]);
    }

    protected function seedListing(int $listingId, int $sellerId, array $overrides = []): void
    {
        db_connect()->table('land_listings')->insert(array_merge([
            'listing_id' => $listingId,
            'seller_id' => $sellerId,
            'title' => 'Prime Lot',
            'description' => 'Test listing',
            'barangay' => 'Looc',
            'city' => 'Nasugbu',
            'province' => 'Batangas',
            'price' => 2500000,
            'developing_area' => 500,
            'property_type' => 'residential_land',
            'road_access_type' => 'cemented',
            'view_type' => 'sea_view',
            'listing_status' => 'available',
            'is_verified_listing' => 'true',
            'is_titled' => 1,
            'has_tax_declaration' => 1,
            'has_lra_approved_plan' => 0,
            'mother_titled_disclosed' => 1,
            'investment_ready' => 1,
            'document_status' => 'complete',
            'created_at' => '2026-04-25 10:00:00',
            'updated_at' => '2026-04-25 10:00:00',
        ], $overrides));
    }

    protected function seedInquiry(int $inquiryId, int $listingId, int $buyerId, int $sellerId, string $status = 'pending'): void
    {
        db_connect()->table('inquiries')->insert([
            'inquiry_id' => $inquiryId,
            'listing_id' => $listingId,
            'buyer_id' => $buyerId,
            'seller_id' => $sellerId,
            'inquiry_status' => $status,
            'created_at' => '2026-04-25 11:00:00',
            'updated_at' => '2026-04-25 12:00:00',
        ]);
    }

    protected function seedSession(int $sessionId, int $listingId, int $inquiryId, int $buyerId, int $sellerId): void
    {
        db_connect()->table('message_sessions')->insert([
            'session_id' => $sessionId,
            'listing_id' => $listingId,
            'inquiry_id' => $inquiryId,
            'buyer_id' => $buyerId,
            'seller_id' => $sellerId,
            'session_status' => 'active',
            'last_message_at' => '2026-04-25 13:00:00',
            'started_at' => '2026-04-25 10:30:00',
        ]);
    }

    protected function seedMessage(int $sessionId, int $senderId, string $messageText, int $isRead = 0): void
    {
        db_connect()->table('messages')->insert([
            'session_id' => $sessionId,
            'sender_id' => $senderId,
            'message_text' => $messageText,
            'attachment_path' => null,
            'is_auto_reply' => 0,
            'is_read' => $isRead,
            'sent_at' => '2026-04-25 13:05:00',
        ]);
    }

    protected function seedFavorite(int $buyerId, int $listingId): void
    {
        db_connect()->table('buyer_favorites')->insert([
            'buyer_id' => $buyerId,
            'listing_id' => $listingId,
        ]);
    }

    protected function invokePrivate(object $object, string $method, array $args = []): mixed
    {
        $reflection = new ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($object, $args);
    }

    protected function setCurrentUser(int $userId): void
    {
        session()->set('user_id', $userId);
        session()->set('UserID', $userId);
    }
}

final class ControllerFunctionChangesTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use LandlyTestSchemaTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetLandlyTestSchema();
        session()->remove('user_id');
        session()->remove('UserID');
    }

    public function testCategoryHelpersAndReplyFormatting(): void
    {
        $controller = new ChatbotController();

        $this->assertSame('residential_land', $this->invokePrivate($controller, 'extractPropertyTypeFromMessage', ['show available residential category in nasugbu']));
        $this->assertSame('agricultural_land', $this->invokePrivate($controller, 'extractPropertyTypeFromMessage', ['list agricultural lots']));
        $this->assertSame('commercial_land', $this->invokePrivate($controller, 'extractPropertyTypeFromMessage', ['show commercial category options']));

        $this->assertTrue($this->invokePrivate($controller, 'isCategoryInquiry', ['show available residential category in nasugbu']));
        $this->assertFalse($this->invokePrivate($controller, 'isCategoryInquiry', ['hello there']));
        $this->assertTrue($this->invokePrivate($controller, 'isPropertyRelatedQuery', ['show available residential category']));
        $this->assertTrue($this->invokePrivate($controller, 'isAvailabilityQuestion', ['what land categories are available']));

        $this->assertSame('Residential', $this->invokePrivate($controller, 'formatPropertyCategoryLabel', ['residential_land']));
        $this->assertSame('agricultural_land', $this->invokePrivate($controller, 'normalizePropertyType', ['farm lot']));

        $listings = [
            ['listing_id' => 1, 'property_type' => 'residential_land'],
            ['listing_id' => 2, 'property_type' => 'commercial_land'],
            ['listing_id' => 3, 'property_type' => 'agricultural_land'],
        ];

        $filtered = $this->invokePrivate($controller, 'filterListingsByPropertyType', [$listings, 'commercial_land']);
        $this->assertCount(1, $filtered);
        $this->assertSame(2, $filtered[0]['listing_id']);

        $listing = [
            'listing_id' => 101,
            'title' => 'Sunrise Lot',
            'description' => 'Prime commercial land',
            'barangay' => 'Looc',
            'city' => 'Nasugbu',
            'province' => 'Batangas',
            'price' => 2500000,
            'developing_area' => 500,
            'property_type' => 'commercial_land',
            'listing_status' => 'available',
            'listing_latitude' => 14.1,
            'listing_longitude' => 120.6,
            'primary_image_url' => 'https://example.com/listing.jpg',
        ];

        $listingObject = $this->invokePrivate($controller, 'buildListingDataObject', [$listing, false]);
        $this->assertSame('commercial_land', $listingObject['property_type']);
        $this->assertSame('Commercial', $listingObject['property_category']);
        $this->assertSame('available', $listingObject['availability']);
        $this->assertTrue($listingObject['is_available']);
        $this->assertSame(14.1, $listingObject['coordinates']['lat']);
        $this->assertSame(120.6, $listingObject['coordinates']['lng']);

        $categoryReply = $this->invokePrivate($controller, 'buildCategoryReply', ['commercial_land', [$listingObject], 'Ana']);
        $this->assertStringContainsString('Hi Ana', $categoryReply);
        $this->assertStringContainsString('Here are the available Commercial listings in Nasugbu', $categoryReply);
        $this->assertStringContainsString('Sunrise Lot', $categoryReply);
        $this->assertStringContainsString('Looc, Nasugbu, Batangas', $categoryReply);
    }

    public function testTrackListingViewCountsAUniqueViewOnlyOncePerDay(): void
    {
        $this->seedUser(1, 'Brenda', 'Buyer', 'buyer@example.com', 1, 'buyer');
        $this->seedListing(10, 1);

        $first = $this->withSession(['user_id' => 1, 'UserID' => 1])->post('buyer/listings/track-view', ['listing_id' => 10]);
        $first->assertStatus(200);
        $first->assertJSONFragment(['counted' => true]);

        $second = $this->withSession(['user_id' => 1, 'UserID' => 1])->post('buyer/listings/track-view', ['listing_id' => 10]);
        $second->assertStatus(200);
        $second->assertJSONFragment(['counted' => false]);

        $db = db_connect();
        $analytics = $db->table('listing_analytics')->where('listing_id', 10)->get()->getRowArray();
        $this->assertNotNull($analytics);
        $this->assertSame(1, (int) ($analytics['total_views'] ?? 0));

        $dailyViews = $db->table('listing_daily_views')->countAllResults();
        $this->assertSame(1, (int) $dailyViews);
    }

    public function testBuyerProfilePayloadAndInquiryPayloadUseBuyerAndSellerLabels(): void
    {
        $this->seedUser(1, 'Brenda', 'Buyer', 'buyer@example.com', 1, 'buyer');
        $this->seedUser(2, 'Sally', 'Seller', 'seller@example.com', 1, 'seller');
        $this->seedListing(20, 2, ['title' => 'Hilltop Estate', 'property_type' => 'agricultural_land']);
        $this->seedInquiry(30, 20, 1, 2, 'accepted');
        $this->seedSession(40, 20, 30, 1, 2);
        $this->seedMessage(40, 2, 'Thanks for your inquiry.', 0);
        $this->seedFavorite(1, 20);

        $controller = new DashboardController();

        $currentUserProfile = $this->invokePrivate($controller, 'getCurrentUserProfile', [1]);
        $this->assertSame('Buyer', $currentUserProfile['status_label']);
        $this->assertSame('active', $currentUserProfile['status_class']);

        $buyerProfile = $this->invokePrivate($controller, 'getBuyerProfilePayload', [1]);
        $this->assertSame('Buyer', $buyerProfile['status_label']);
        $this->assertSame('active', $buyerProfile['status_class']);
        $this->assertSame(1, (int) ($buyerProfile['stats']['saved_properties'] ?? 0));
        $this->assertSame(1, (int) ($buyerProfile['stats']['accepted_inquiries'] ?? 0));
        $this->assertSame(1, (int) ($buyerProfile['stats']['unread_messages'] ?? 0));

        $inquiries = $this->invokePrivate($controller, 'getBuyerInquiriesPayload', [1]);
        $this->assertCount(1, $inquiries);
        $this->assertSame('Sally Seller', $inquiries[0]['seller_name']);
        $this->assertSame('SS', $inquiries[0]['seller_initials']);
        $this->assertSame('Hilltop Estate', $inquiries[0]['title']);
        $this->assertSame('Accepted', $inquiries[0]['status_label']);
    }

    public function testBuyerProfileEndpointReturnsBuyerStatusLabel(): void
    {
        $this->seedUser(1, 'Brenda', 'Buyer', 'buyer@example.com', 1, 'buyer');

        $result = $this->withSession(['user_id' => 1, 'UserID' => 1])->get('buyer/profile');
        $result->assertStatus(200);
        $result->assertJSONFragment(['status' => 'success']);
        $result->assertJSONFragment(['status_label' => 'Buyer']);
    }

    public function testMessageSessionsIncludeBuyerAndSellerNames(): void
    {
        $this->seedUser(1, 'Brenda', 'Buyer', 'buyer@example.com', 1, 'buyer');
        $this->seedUser(2, 'Sally', 'Seller', 'seller@example.com', 1, 'seller');
        $this->seedListing(10, 2, ['title' => 'Hilltop Estate']);
        $this->seedInquiry(20, 10, 1, 2, 'pending');
        $this->seedSession(30, 10, 20, 1, 2);
        $this->seedMessage(30, 2, 'Hello buyer', 0);

        $result = $this->withSession(['user_id' => 1, 'UserID' => 1])->get('messages/sessions');
        $result->assertStatus(200);
        $result->assertJSONFragment(['status' => 'success']);
        $result->assertJSONFragment(['buyer_name' => 'Brenda Buyer']);
        $result->assertJSONFragment(['seller_name' => 'Sally Seller']);
        $result->assertJSONFragment(['unread_count' => 1]);
    }
}