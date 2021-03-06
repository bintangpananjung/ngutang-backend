<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');
$routes->post('/login', 'AuthController::login');
$routes->post('/register', 'AuthController::register');
$routes->group("", ['filter' => 'auth'], function ($routes) {
    $routes->get('/profile', 'AuthController::profile');
    $routes->get('/history', 'UtangController::history');
    $routes->get('/history/pages/(:num)', 'UtangController::history/$1');
    $routes->get('/history/(:alphanum)', 'UtangController::historyUser/$1');
    $routes->get('/transaction', 'UtangController::index');
    $routes->post('/transaction/add', 'UtangController::add');
    $routes->get('/transaction/pages/(:num)', 'UtangController::index/$1');
    $routes->get('/transaction/(:alphanum)', 'UtangController::userTransaction/$1');
    $routes->get('/member', 'UserController::index');
    $routes->post('/notify-bill', 'EmailController::notifybill');
    $routes->post('/notify-paid-off', 'EmailController::notifypaidoff');
});
$routes->get('/cekemail', "EmailController::index");
/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
