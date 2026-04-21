<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// =============================================================================
// PUBLIC — Auth (no filter)
// =============================================================================
$routes->get('/',                                  'Auth::login');
$routes->get('auth/login',                         'Auth::login');
$routes->post('auth/login',                        'Auth::authenticate');
$routes->post('login',                             'Auth::authenticate');
$routes->get('auth/logout',                        'Auth::logout');
$routes->get('auth/forgot-password',               'Auth::forgotPassword');
$routes->post('auth/forgot-password',              'Auth::sendResetLink');
$routes->get('auth/reset-password/(:alphanum)',     'Auth::resetPassword/$1');
$routes->post('auth/reset-password',               'Auth::doResetPassword');

// =============================================================================
// PROTECTED — all routes below require a valid session (AuthFilter)
// =============================================================================
$routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes) {

    // Dashboard
    $routes->get('dashboard',                      'Dashboard::index');
    $routes->get('Dashboard/memberProducts',        'Dashboard::memberProducts');

    // Products
    $routes->get('Controller_Products',                                  'Controller_Products::index');
    $routes->get('Controller_Products/fetchProductData',                 'Controller_Products::fetchProductData');
    $routes->match(['get', 'post'], 'Controller_Products/create',        'Controller_Products::create');
    $routes->match(['get', 'post'], 'Controller_Products/update/(:num)', 'Controller_Products::update/$1');
    $routes->get('Controller_Products/printProduct/(:num)',              'Controller_Products::printProduct/$1');
    $routes->get('Controller_Products/productReceipt/(:num)',            'Controller_Products::productReceipt/$1');
    $routes->post('Controller_Products/remove',                          'Controller_Products::remove');
    $routes->post('Controller_Products/bulkRemove',                      'Controller_Products::bulkRemove');

    // Orders
    $routes->get('Controller_Orders',                                  'Controller_Orders::index');
    $routes->get('Controller_Orders/fetchOrdersData',                  'Controller_Orders::fetchOrdersData');
    $routes->match(['get', 'post'], 'Controller_Orders/create',        'Controller_Orders::create');
    $routes->match(['get', 'post'], 'Controller_Orders/update/(:num)', 'Controller_Orders::update/$1');
    $routes->post('Controller_Orders/remove',                          'Controller_Orders::remove');
    $routes->post('Controller_Orders/bulkRemove',                      'Controller_Orders::bulkRemove');
    $routes->post('Controller_Orders/getProductValueById',             'Controller_Orders::getProductValueById');
    $routes->post('Controller_Orders/getTableProductRow',              'Controller_Orders::getTableProductRow');
    $routes->post('Controller_Orders/getProductByIMEI',                'Controller_Orders::getProductByIMEI');
    $routes->post('Controller_Orders/returnToStock',                   'Controller_Orders::returnToStock');
    $routes->get('Controller_Orders/printDiv/(:num)',                   'Controller_Orders::printDiv/$1');
    $routes->get('Controller_Orders/downloadPDF/(:num)',                'Controller_Orders::downloadPDF/$1');

    // Warehouse
    $routes->get('Controller_Warehouse',                                     'Controller_Warehouse::index');
    $routes->get('Controller_Warehouse/fetchStoresData',                     'Controller_Warehouse::fetchStoresData');
    $routes->get('Controller_Warehouse/fetchStoresDataById/(:num)',          'Controller_Warehouse::fetchStoresDataById/$1');
    $routes->post('Controller_Warehouse/create',                             'Controller_Warehouse::create');
    $routes->post('Controller_Warehouse/update/(:num)',                      'Controller_Warehouse::update/$1');
    $routes->post('Controller_Warehouse/remove',                             'Controller_Warehouse::remove');

    // Members
    $routes->get('Controller_Members',                                     'Controller_Members::index');
    $routes->match(['get', 'post'], 'Controller_Members/create',           'Controller_Members::create');
    $routes->match(['get', 'post'], 'Controller_Members/edit/(:num)',      'Controller_Members::edit/$1');
    $routes->match(['get', 'post'], 'Controller_Members/delete/(:num)',    'Controller_Members::delete/$1');
    $routes->get('Controller_Members/profile',                             'Controller_Members::profile');
    $routes->match(['get', 'post'], 'Controller_Members/setting',         'Controller_Members::setting');

    // Permissions
    $routes->get('Controller_Permission',                                    'Controller_Permission::index');
    $routes->match(['get', 'post'], 'Controller_Permission/create',          'Controller_Permission::create');
    $routes->match(['get', 'post'], 'Controller_Permission/edit/(:num)',     'Controller_Permission::edit/$1');
    $routes->match(['get', 'post'], 'Controller_Permission/delete/(:num)',   'Controller_Permission::delete/$1');

    // Company
    $routes->match(['get', 'post'], 'Controller_Company', 'Controller_Company::index');

    // Reports
    $routes->match(['get', 'post'], 'Reports', 'Reports::index');

    // Notifications
    $routes->get('Controller_Notifications/poll',       'Controller_Notifications::poll');
    $routes->post('Controller_Notifications/markRead',  'Controller_Notifications::markRead');
});