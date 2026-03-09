<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('auth', 'Home::auth');

$routes->group('auth', function ($routes) {
    $routes->post('login', 'Auth\AuthController::login');
    $routes->post('signup/buyer', 'Auth\SignUpController::buyerSignUp');
    $routes->post('signup/seller', 'Auth\SignUpController::sellerSignUp');
});

// Seller land listing CRUD routes
$routes->group('seller', function ($routes) {

    // Dashboard route
    $routes->get('dashboard', 'Seller\DashboardController::index');

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

    // Land listing routes for buyers
    $routes->get('listings', 'Buyer\LandListingController::listAll');
    $routes->get('listings/(:num)', 'Buyer\LandListingController::view/$1');
    
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
});


// Messaging routes
$routes->group('messages', function ($routes) {
    $routes->get('sessions', 'Messages\MessageController::getSessions');
    $routes->post('sessions/start', 'Messages\MessageController::startSession');
    $routes->get('sessions/(:num)', 'Messages\MessageController::getMessages/$1');
    $routes->post('send', 'Messages\MessageController::sendMessage');
});


