<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('auth', 'Home::auth');
$routes->get('media/profile', 'MediaController::profile');
$routes->get('listings/filter', 'Buyer\DashboardController::filterListings');

$routes->group('auth', function ($routes) {
    $routes->post('login', 'Auth\AuthController::login');
    $routes->post('signup/buyer', 'Auth\SignUpController::buyerSignUp');
    $routes->post('signup/seller', 'Auth\SignUpController::sellerSignUp');
});

// Seller land listing CRUD routes
$routes->group('seller', function ($routes) {

    // Dashboard route
    $routes->get('dashboard', 'Seller\DashboardController::index');
    $routes->get('sidebar-counts', 'Seller\DashboardController::sidebarCounts');
    $routes->get('dashboard-section', 'Seller\DashboardController::dashboardSection');

    // Land listing CRUD routes for sellers
    $routes->post('listings', 'Seller\LandListingCRUDController::createLandListing');
    $routes->get('listings/(:num)', 'Seller\LandListingCRUDController::readLandListing/$1');
    $routes->put('listings/(:num)', 'Seller\LandListingCRUDController::updateLandListing/$1');
    $routes->delete('listings/(:num)', 'Seller\LandListingCRUDController::deleteLandListing/$1');
});

// Buyer routes
$routes->group('buyer', function ($routes) {
    // Dashboard route
    $routes->get('dashboard', 'Buyer\DashboardController::index');
    $routes->get('sidebar-counts', 'Buyer\DashboardController::sidebarCounts');

    // Buyer profile CRUD routes
    $routes->get('profile', 'Buyer\BuyerProfileController::index');
    $routes->get('profile/(:num)', 'Buyer\BuyerProfileController::show/$1');
    $routes->post('profile', 'Buyer\BuyerProfileController::store');
    $routes->put('profile/(:num)', 'Buyer\BuyerProfileController::update/$1');
    $routes->delete('profile/(:num)', 'Buyer\BuyerProfileController::delete/$1');
    $routes->post('profile/reset', 'Buyer\BuyerProfileController::delete');

    // Land listing routes for buyers
    $routes->get('listings', 'Buyer\LandListingController::listAll');
    $routes->get('listings/(:num)', 'Buyer\LandListingController::view/$1');
    
    // Favorites routes
    $routes->get('favorites', 'Buyer\Favorites::index');
    $routes->post('favorites/toggle', 'Buyer\Favorites::toggle');
    $routes->post('favorites/is-favorited', 'Buyer\Favorites::isFavorited');
    $routes->get('favorites/get-all', 'Buyer\Favorites::getBuyerFavorites');

    // Chatbot routes
    $routes->post('chatbot/send-message', 'Buyer\ChatbotController::sendMessage');
});

//testing controller for auth routes
$routes->group('test', function ($routes) {
    $routes->get('auth', 'Test\TestAuth::testLogin');
    $routes->post('signup/buyer', 'Test\TestAuth::testBuyerSignUp');
    $routes->post('signup/seller', 'Test\TestAuth::testSellerSignUp');
    $routes->get('seller/login', 'Test\TestAuth::sellertestLogin');
    $routes->get('buyer/login', 'Test\TestAuth::buyertestLogin');
    //test routes for land listing CRUD
    $routes->post('seller/listings', 'Test\TestLandListingCrud::testCreateLandListing');

    // test routes for message controller
    $routes->get('messages/set-user/(:num)', 'Test\MessageTest::testSetUser/$1');
    $routes->post('messages/set-user', 'Test\MessageTest::testSetUser');
    $routes->get('messages/sessions', 'Test\MessageTest::testGetSessions');
    $routes->post('messages/sessions/start', 'Test\MessageTest::testStartSession');
    $routes->get('messages/sessions/(:num)', 'Test\MessageTest::testGetMessages/$1');
    $routes->post('messages/send', 'Test\MessageTest::testSendMessage');
    $routes->post('messages/send-1-2', 'Test\MessageTest::testSendBetweenUsersOneAndTwo');

    // test routes for inquiries controller
    $routes->get('inquiries', 'Test\MessageTest::testListInquiries');
    $routes->get('inquiries/(:num)', 'Test\MessageTest::testViewInquiry/$1');
    $routes->post('inquiries', 'Test\MessageTest::testCreateInquiry');
    $routes->put('inquiries/(:num)/status', 'Test\MessageTest::testUpdateInquiryStatus/$1');
});


// Messaging routes
$routes->group('messages', function ($routes) {
    $routes->get('sessions', 'Messages\MessageController::getSessions');
    $routes->post('sessions/start', 'Messages\MessageController::startSession');
    $routes->get('sessions/(:num)', 'Messages\MessageController::getMessages/$1');
    $routes->post('send', 'Messages\MessageController::sendMessage');

    $routes->get('inquiries', 'Messages\InquriesController::listInquiries');
    $routes->get('inquiries/(:num)', 'Messages\InquriesController::viewInquiry/$1');
    $routes->post('inquiries', 'Messages\InquriesController::createInquiry');
    $routes->put('inquiries/(:num)/status', 'Messages\InquriesController::updateInquiryStatus/$1');
});

// Notifications routes (shared by buyer and seller)
$routes->group('notifications', function ($routes) {
    $routes->get('/', 'Notification\NotificationController::getNotifications');
    $routes->get('unread-count', 'Notification\NotificationController::getUnreadCount');
    $routes->get('changes', 'Notification\NotificationController::checkChanges');
    $routes->patch('read-all', 'Notification\NotificationController::markAllAsRead');
    $routes->patch('(:num)/read', 'Notification\NotificationController::markAsRead/$1');
    $routes->patch('(:num)/archive', 'Notification\NotificationController::archiveNotification/$1');
});

// Admin routes
$routes->group('admin', function ($routes) {
    $routes->get('dashboard', 'Admin\DashboardController::index');
    $routes->get('listings/(:num)/view', 'Admin\DashboardController::viewListing/$1');
    $routes->post('users/(:num)/activate', 'Admin\DashboardController::activateUser/$1');
    $routes->post('users/(:num)/deactivate', 'Admin\DashboardController::deactivateUser/$1');
    $routes->post('users/(:num)/delete', 'Admin\DashboardController::deleteUser/$1');

    $routes->post('listings/(:num)/verify', 'Admin\DashboardController::verifyListing/$1');
    $routes->post('listings/(:num)/reject', 'Admin\DashboardController::rejectListing/$1');
    $routes->post('listings/(:num)/delete', 'Admin\DashboardController::deleteListing/$1');
});

