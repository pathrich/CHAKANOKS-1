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

// Supply Request Routes
$routes->get('supply-request', 'SupplyRequest::index', ['filter' => 'auth']);
$routes->post('supply-request/submit', 'SupplyRequest::submit', ['filter' => 'auth']);
$routes->post('supply-request/approve', 'SupplyRequest::approve', ['filter' => 'auth']);
$routes->post('supply-request/reject', 'SupplyRequest::reject', ['filter' => 'auth']);
$routes->get('supply-request/pending-count', 'SupplyRequest::getPendingCount', ['filter' => 'auth']);
$routes->get('supply-request/my-requests', 'SupplyRequest::myRequests', ['filter' => 'auth']);

// Order Routes
$routes->get('order', 'Order::index', ['filter' => 'auth']);
$routes->get('order/create', 'Order::create', ['filter' => 'auth']);
$routes->post('order/store', 'Order::store', ['filter' => 'auth']);
$routes->post('order/submit', 'Order::submit', ['filter' => 'auth']);
$routes->post('order/approve', 'Order::approve', ['filter' => 'auth']);
$routes->post('order/cancel', 'Order::cancel', ['filter' => 'auth']);
$routes->get('order/pending', 'Order::pending', ['filter' => 'auth']);

// API Routes
$routes->get('api/items', 'Api\Items::list', ['filter' => 'auth']);
$routes->get('api/notifications', 'Api\Notifications::list', ['filter' => 'auth']);
$routes->post('api/notifications/(:num)/read', 'Api\Notifications::markRead/$1', ['filter' => 'auth']);

// Purchase Order Routes
$routes->get('purchase-order', 'PurchaseOrder::index', ['filter' => 'auth']);
$routes->post('purchase-order/supplier-accept', 'PurchaseOrder::supplierAccept', ['filter' => 'auth']);
$routes->post('purchase-order/supplier-request-changes', 'PurchaseOrder::supplierRequestChanges', ['filter' => 'auth']);
$routes->post('purchase-order/supplier-decline', 'PurchaseOrder::supplierDecline', ['filter' => 'auth']);
$routes->post('purchase-order/supplier-ship', 'PurchaseOrder::supplierShip', ['filter' => 'auth']);
$routes->post('purchase-order/mark-delivered', 'PurchaseOrder::markDelivered', ['filter' => 'auth']);
// Delivery / Logistics routes (logistics_coordinator only)
$routes->get('deliveries', 'Delivery::index', ['filter' => 'role:logistics_coordinator']);
$routes->get('deliveries/create', 'Delivery::create', ['filter' => 'role:logistics_coordinator']);
$routes->post('deliveries/store', 'Delivery::store', ['filter' => 'role:logistics_coordinator']);
$routes->get('deliveries/track/(:num)', 'Delivery::track/$1', ['filter' => 'role:logistics_coordinator']);
$routes->post('deliveries/optimize', 'Delivery::optimizeRoute', ['filter' => 'role:logistics_coordinator']);
