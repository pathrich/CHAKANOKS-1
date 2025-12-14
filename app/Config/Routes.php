<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::login');
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::doLogin');
$routes->get('logout', 'Auth::logout');
// Switch active role for logged-in user
$routes->post('switch-role', 'Auth::switchRole', ['filter' => 'auth']);

$routes->get('dashboard', 'Dashboard::index', ['filter' => 'auth']);

$routes->get('inventory', 'Inventory::index', ['filter' => 'role:inventory_staff,branch_manager,central_admin,system_admin']);
$routes->post('inventory/receive', 'Inventory::receive', ['filter' => 'role:inventory_staff,branch_manager,central_admin,system_admin']);
$routes->post('inventory/adjust', 'Inventory::adjust', ['filter' => 'role:inventory_staff,branch_manager,central_admin,system_admin']);
// Mark stock as expired or damaged and view history (branch-level)
$routes->post('inventory/expired', 'Inventory::markExpired', ['filter' => 'role:inventory_staff,branch_manager,central_admin,system_admin']);
$routes->post('inventory/damaged', 'Inventory::markDamaged', ['filter' => 'role:inventory_staff,branch_manager,central_admin,system_admin']);
$routes->get('inventory/history/(:num)', 'Inventory::history/$1', ['filter' => 'role:inventory_staff,branch_manager,central_admin,system_admin']);
// Request inter-branch transfer from inventory
$routes->post('inventory/transfer-request', 'Inventory::requestTransfer', ['filter' => 'role:inventory_staff,branch_manager,central_admin,system_admin']);
// Acknowledge low stock alerts from inventory
$routes->post('inventory/ack-low', 'Inventory::acknowledgeLowStock', ['filter' => 'role:inventory_staff,branch_manager,central_admin,system_admin']);

// Supply Request Routes
$routes->get('supply-request', 'SupplyRequest::index', ['filter' => 'auth']);
$routes->get('supply-request/create', 'SupplyRequest::create', ['filter' => 'role:branch_manager,inventory_staff']);
$routes->post('supply-request/submit', 'SupplyRequest::submit', ['filter' => 'auth']);
$routes->post('supply-request/approve', 'SupplyRequest::approve', ['filter' => 'auth']);
$routes->post('supply-request/reject', 'SupplyRequest::reject', ['filter' => 'auth']);
$routes->get('supply-request/all', 'SupplyRequest::allRequests', ['filter' => 'role:central_admin,system_admin']);
$routes->get('supply-request/pending-count', 'SupplyRequest::getPendingCount', ['filter' => 'auth']);
$routes->get('supply-request/my-requests', 'SupplyRequest::myRequests', ['filter' => 'auth']);

// Transfer Routes
$routes->get('transfers', 'Transfers::index', ['filter' => 'role:central_admin']);
$routes->post('transfers/approve', 'Transfers::approve', ['filter' => 'role:central_admin']);
$routes->post('transfers/reject', 'Transfers::reject', ['filter' => 'role:central_admin']);
$routes->get('transfers/my', 'Transfers::my', ['filter' => 'auth']);

// Order Routes
$routes->get('order', 'Order::index', ['filter' => 'auth']);
$routes->get('order/create', 'Order::create', ['filter' => 'auth']);
$routes->post('order/store', 'Order::store', ['filter' => 'auth']);
$routes->post('order/submit', 'Order::submit', ['filter' => 'auth']);
$routes->post('order/approve', 'Order::approve', ['filter' => 'role:central_admin,system_admin']);
$routes->post('order/cancel', 'Order::cancel', ['filter' => 'auth']);
$routes->get('order/pending', 'Order::pending', ['filter' => 'role:central_admin,system_admin']);

// API Routes
// Items API used by manager order creation screen
$routes->get('api/items', 'Api\Items::list', ['filter' => 'auth']);
$routes->get('api/notifications', 'Api\Notifications::list', ['filter' => 'auth']);
$routes->post('api/notifications/(:num)/read', 'Api\Notifications::markRead/$1', ['filter' => 'auth']);

// Purchase Order Routes
$routes->get('purchase-order', 'PurchaseOrder::index', ['filter' => 'auth']);
$routes->get('purchase-order/supplier', 'PurchaseOrder::supplierPortal', ['filter' => 'role:supplier']);
$routes->get('purchase-order/supplier/pos', 'PurchaseOrder::supplierList', ['filter' => 'role:supplier']);
$routes->post('purchase-order/supplier-accept', 'PurchaseOrder::supplierAccept', ['filter' => 'role:supplier']);
$routes->post('purchase-order/supplier-request-changes', 'PurchaseOrder::supplierRequestChanges', ['filter' => 'role:supplier']);
$routes->post('purchase-order/supplier-decline', 'PurchaseOrder::supplierDecline', ['filter' => 'role:supplier']);
$routes->post('purchase-order/supplier-ship', 'PurchaseOrder::supplierShip', ['filter' => 'role:supplier']);
$routes->post('purchase-order/mark-delivered', 'PurchaseOrder::markDelivered', ['filter' => 'role:central_admin,system_admin']);
// Delivery / Logistics routes (logistics_coordinator only)
$routes->get('deliveries', 'Delivery::index', ['filter' => 'role:logistics_coordinator']);
$routes->get('deliveries/create', 'Delivery::create', ['filter' => 'role:logistics_coordinator']);
$routes->post('deliveries/store', 'Delivery::store', ['filter' => 'role:logistics_coordinator']);
$routes->get('deliveries/track/(:num)', 'Delivery::track/$1', ['filter' => 'role:logistics_coordinator']);
$routes->post('deliveries/optimize', 'Delivery::optimizeRoute', ['filter' => 'role:logistics_coordinator']);
// Mark delivery as delivered and update inventory for transfers
$routes->post('deliveries/mark-delivered', 'Delivery::markDelivered', ['filter' => 'role:logistics_coordinator']);

// System Administrator routes
$routes->get('system-admin', 'SystemAdmin::index', ['filter' => 'role:system_admin']);
$routes->get('system-admin/users', 'SystemAdmin::users', ['filter' => 'role:system_admin']);
$routes->get('system-admin/backups', 'SystemAdmin::backups', ['filter' => 'role:system_admin']);
$routes->get('system-admin/security', 'SystemAdmin::security', ['filter' => 'role:system_admin']);

// Item management routes (admin)
$routes->get('items', 'Items::index', ['filter' => 'role:central_admin,system_admin']);
$routes->post('items/store', 'Items::store', ['filter' => 'role:central_admin,system_admin']);
