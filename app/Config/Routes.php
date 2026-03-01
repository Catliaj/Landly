<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('auth', function ($routes) {
    $routes->post('login', 'Auth\AuthController::login');
    $routes->post('signup/buyer', 'Auth\SignUpController::buyerSignUp');
    $routes->post('signup/seller', 'Auth\SignUpController::sellerSignUp');
});

//testing controller for auth routes
$routes->group('test', function ($routes) {
    $routes->get('auth', 'Test\TestAuth::testLogin');
    $routes->post('signup/buyer', 'Test\TestAuth::testBuyerSignUp');
    $routes->post('signup/seller', 'Test\TestAuth::testSellerSignUp');
    $routes->get('seller/login', 'Test\TestAuth::sellertestLogin');
    $routes->get('buyer/login', 'Test\TestAuth::buyertestLogin');
});