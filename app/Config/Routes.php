<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::login');
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::doLogin');
$routes->get('logout', 'Auth::logout');

$routes->get('dashboard', 'Dashboard::index', ['filter' => 'auth']);

$routes->get('inventory', 'Inventory::index', ['filter' => 'role:inventory_staff']);
$routes->post('inventory/receive', 'Inventory::receive', ['filter' => 'role:inventory_staff']);
$routes->post('inventory/adjust', 'Inventory::adjust', ['filter' => 'role:inventory_staff']);
