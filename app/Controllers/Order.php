<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\NotificationModel;
use App\Models\PurchaseOrderModel;

class Order extends BaseController
{
    protected $orderModel;
    protected $notificationModel;
    protected $purchaseOrderModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->notificationModel = new NotificationModel();
        $this->purchaseOrderModel = new PurchaseOrderModel();
    }

    private function generatePONumber(): string
    {
        $prefix = 'PO-' . date('Ymd');
        $db = db_connect();
        $count = $db->table('purchase_orders')->like('po_number', $prefix, 'after')->countAllResults();
        return sprintf('%s-%04d', $prefix, $count + 1);
    }

    private function getDefaultSupplierId(): ?int
    {
        $db = db_connect();
        if (! $db->tableExists('suppliers')) {
            return null;
        }
        $row = $db->table('suppliers')->select('id')->orderBy('id', 'ASC')->get()->getRowArray();
        return $row ? (int) $row['id'] : null;
    }

    private function notifySupplierUsers(int $supplierId, string $title, string $message, ?int $relatedId = null): void
    {
        $db = db_connect();
        if (! $db->tableExists('user_suppliers')) {
            return;
        }

        $rows = $db->table('user_suppliers')->select('user_id')->where('supplier_id', $supplierId)->get()->getResultArray();
        foreach ($rows as $r) {
            $uid = (int) ($r['user_id'] ?? 0);
            if ($uid <= 0) {
                continue;
            }
            $this->notificationModel->createNotification(
                $uid,
                'order_approved_po_created',
                $title,
                $message,
                $relatedId,
                'purchase_order'
            );
        }
    }

    public function update()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX request required']);
        }

        if (!session('user_id')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not authenticated']);
        }

        $db = db_connect();

        // Resolve branch
        $branchId = (int)(session('branch_id') ?? 0);
        if (!$branchId) {
            $user = $db->table('users')
                ->select('branch_id')
                ->where('id', session('user_id'))
                ->get()
                ->getRowArray();
            $branchId = (int)($user['branch_id'] ?? 0);
        }

        if (!$branchId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'User has no assigned branch']);
        }

        $json = $this->request->getJSON();
        $orderId = (int)($json->order_id ?? 0);
        $items = $json->items ?? [];
        $notes = $json->notes ?? null;

        if ($orderId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid order ID']);
        }

        if (empty($items)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'No items provided']);
        }

        $order = $this->orderModel->find($orderId);
        if (!$order) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Order not found']);
        }

        if ((int)$order['branch_id'] !== $branchId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'You are not allowed to update this order']);
        }

        if (($order['status'] ?? '') !== 'Draft') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Only Draft orders can be updated']);
        }

        $db->transBegin();
        try {
            $totalItems = 0;
            $totalAmount = 0;

            foreach ($items as $item) {
                $totalItems += (int) ($item->quantity ?? 0);
                $totalAmount += ((float) ($item->quantity ?? 0)) * ((float) ($item->unit_price ?? 0));
            }

            $db->table('orders')->update([
                'total_items' => $totalItems,
                'total_amount' => $totalAmount,
                'notes' => $notes,
                'updated_at' => date('Y-m-d H:i:s'),
            ], ['id' => $orderId]);

            // Replace line items
            $db->table('order_items')->where('order_id', $orderId)->delete();
            foreach ($items as $item) {
                $itemId = (int) ($item->item_id ?? 0);
                $qty = (int) ($item->quantity ?? 0);
                $price = (float) ($item->unit_price ?? 0);
                if ($itemId <= 0 || $qty <= 0) continue;
                $db->table('order_items')->insert([
                    'order_id' => $orderId,
                    'item_id' => $itemId,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'subtotal' => $qty * $price,
                    'notes' => $item->notes ?? null,
                ]);
            }

            $db->table('activity_logs')->insert([
                'user_id' => session('user_id'),
                'action' => 'order_updated',
                'details' => 'Order #' . $orderId . ' updated',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $db->transCommit();
            return $this->response->setJSON(['success' => true, 'message' => 'Order updated']);
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Order update failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display orders dashboard for manager
     */
    public function index()
    {
        if (!session('user_id')) {
            return redirect()->to('/login');
        }

        $db = db_connect();

        // Get user's branch (prefer session, fallback to DB)
        $branchId = (int)(session('branch_id') ?? 0);
        if (!$branchId) {
            $user = $db->table('users')
                       ->select('branch_id')
                       ->where('id', session('user_id'))
                       ->get()
                       ->getRowArray();
            $branchId = (int)($user['branch_id'] ?? 0);
        }

        if (!$branchId) {
            return view('dashboard/unauthorized', [
                'userRoles' => ['branch_manager'],
                'errorMessage' => 'Your account is not linked to any branch. Please ask the System Admin to assign a branch.',
            ]);
        }

        // Get orders for this branch
        $orders = $this->orderModel->getByBranch($branchId);

        // Get detailed items for each order
        foreach ($orders as &$order) {
            $order = $this->orderModel->getWithItems($order['id']);
        }

        $data = [
            'orders'   => $orders,
            'branchId' => $branchId,
            'title'    => 'Orders Management',
        ];

        return view('order/manager_dashboard', $data);
    }

    /**
     * Show order creation form
     */
    public function create()
    {
        if (!session('user_id')) {
            return redirect()->to('/login');
        }

        $db = db_connect();

        // Get user's branch (prefer session, fallback to DB)
        $branchId = (int)(session('branch_id') ?? 0);
        if (!$branchId) {
            $user = $db->table('users')
                       ->select('branch_id')
                       ->where('id', session('user_id'))
                       ->get()
                       ->getRowArray();
            $branchId = (int)($user['branch_id'] ?? 0);
        }

        if (!$branchId) {
            return view('dashboard/unauthorized', [
                'userRoles' => ['branch_manager'],
                'errorMessage' => 'Your account is not linked to any branch. Please ask the System Admin to assign a branch.',
            ]);
        }

        $data = [
            'branchId' => $branchId,
            'title'    => 'Create New Order',
        ];

        return view('order/create', $data);
    }

    public function edit($id)
    {
        if (!session('user_id')) {
            return redirect()->to('/login');
        }

        $orderId = (int) $id;
        if ($orderId <= 0) {
            return redirect()->to(site_url('order'))->with('error', 'Invalid order');
        }

        $db = db_connect();

        // Resolve branch
        $branchId = (int)(session('branch_id') ?? 0);
        if (!$branchId) {
            $user = $db->table('users')
                ->select('branch_id')
                ->where('id', session('user_id'))
                ->get()
                ->getRowArray();
            $branchId = (int)($user['branch_id'] ?? 0);
        }

        if (!$branchId) {
            return view('dashboard/unauthorized', [
                'userRoles' => ['branch_manager'],
                'errorMessage' => 'Your account is not linked to any branch. Please ask the System Admin to assign a branch.',
            ]);
        }

        $order = $this->orderModel->getWithItems($orderId);
        if (!$order) {
            return redirect()->to(site_url('order'))->with('error', 'Order not found');
        }

        if ((int)$order['branch_id'] !== $branchId) {
            return redirect()->to(site_url('order'))->with('error', 'You are not allowed to edit this order');
        }

        if (($order['status'] ?? '') !== 'Draft') {
            return redirect()->to(site_url('order'))->with('error', 'Only Draft orders can be edited');
        }

        return view('order/edit', [
            'title' => 'Edit Order',
            'order' => $order,
        ]);
    }

    /**
     * Store new order
     */
    public function store()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX request required']);
        }

        if (!session('user_id')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not authenticated']);
        }

        $db = db_connect();

        // Get user's branch
        $user = $db->table('users')
                   ->select('branch_id')
                   ->where('id', session('user_id'))
                   ->get()
                   ->getRowArray();

        if (!$user || !$user['branch_id']) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'User has no assigned branch']);
        }

        $json = $this->request->getJSON();
        $items = $json->items ?? [];
        $notes = $json->notes ?? null;

        if (empty($items)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'No items provided']);
        }

        $db->transBegin();

        try {
            // Calculate totals
            $totalItems = 0;
            $totalAmount = 0;

            foreach ($items as $item) {
                $totalItems += $item->quantity;
                $totalAmount += $item->quantity * $item->unit_price;
            }

            // Generate order number
            $orderNumber = $this->orderModel->generateOrderNumber($user['branch_id']);

            // Create order
            $orderId = $this->orderModel->insert([
                'branch_id'     => $user['branch_id'],
                'created_by'    => session('user_id'),
                'status'        => 'Draft',
                'order_number'  => $orderNumber,
                'total_items'   => $totalItems,
                'total_amount'  => $totalAmount,
                'notes'         => $notes,
            ]);

            if (!$orderId) {
                throw new \Exception('Failed to create order');
            }

            // Add order items
            foreach ($items as $item) {
                $db->table('order_items')->insert([
                    'order_id'   => $orderId,
                    'item_id'    => $item->item_id,
                    'quantity'   => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal'   => $item->quantity * $item->unit_price,
                    'notes'      => $item->notes ?? null,
                ]);
            }

            $db->transCommit();

            return $this->response->setJSON([
                'success'     => true,
                'orderId'     => $orderId,
                'orderNumber' => $orderNumber,
                'message'     => 'Order created successfully',
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Order creation failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }



    /**
     * Submit order (manager -> Pending)
     */
    public function submit()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX request required']);
        }

        if (!session('user_id')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not authenticated']);
        }

        $json = $this->request->getJSON();
        $orderId = (int)($json->order_id ?? 0);

        if (!$orderId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid order ID']);
        }

        try {
            $this->orderModel->submitOrder($orderId, session('user_id'));

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Order submitted for approval',
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Order submit failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }



    /**
     * Approve order (admin only)
     */
    public function approve()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX request required']);
        }

        if (!session('user_id')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not authenticated']);
        }

        $json = $this->request->getJSON(true);
        $orderId = (int)($json['order_id'] ?? 0);

        if (!$orderId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid order ID']);
        }

        try {
            $db = db_connect();
            $db->transBegin();

            $this->orderModel->approveOrder($orderId, session('user_id'));

            if (! $db->tableExists('purchase_orders') || ! $db->tableExists('purchase_order_items')) {
                throw new \Exception('Purchase order tables not found. Please run migrations.');
            }

            if (! $db->fieldExists('order_id', 'purchase_orders') || ! $db->fieldExists('branch_id', 'purchase_orders')) {
                throw new \Exception('Purchase order is missing order linkage. Please run latest migrations.');
            }

            $existing = $db->table('purchase_orders')->select('id')->where('order_id', $orderId)->get()->getRowArray();
            if (!$existing) {
                $order = $this->orderModel->getWithItems($orderId);
                if (!$order) {
                    throw new \Exception('Order not found');
                }

                $supplierId = $this->getDefaultSupplierId();
                $poNumber = $this->generatePONumber();

                $poId = $db->table('purchase_orders')->insert([
                    'order_id' => $orderId,
                    'branch_id' => (int) ($order['branch_id'] ?? 0),
                    'supplier_id' => $supplierId,
                    'created_by' => (int) session('user_id'),
                    'status' => 'PO_CREATED',
                    'po_number' => $poNumber,
                    'total_items' => (int) ($order['total_items'] ?? 0),
                    'total_amount' => (float) ($order['total_amount'] ?? 0),
                    'notes' => $order['notes'] ?? null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ], true);

                if (!$poId) {
                    throw new \Exception('Failed to create purchase order');
                }

                $poId = (int) $db->insertID();

                $items = $order['items'] ?? [];
                foreach ($items as $it) {
                    $qty = (int) ($it['quantity'] ?? 0);
                    $price = (float) ($it['unit_price'] ?? 0);
                    $itemId = (int) ($it['item_id'] ?? 0);
                    if ($qty <= 0 || $itemId <= 0) {
                        continue;
                    }
                    $db->table('purchase_order_items')->insert([
                        'purchase_order_id' => $poId,
                        'item_id' => $itemId,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'subtotal' => $qty * $price,
                        'notes' => $it['notes'] ?? null,
                    ]);
                }

                if ($supplierId) {
                    $this->notifySupplierUsers(
                        (int) $supplierId,
                        'New Purchase Order: ' . $poNumber,
                        'A new purchase order has been created from an approved order and needs your action.',
                        $poId
                    );
                }
            }

            $db->transCommit();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Order approved',
            ]);
        } catch (\Exception $e) {
            if (isset($db) && $db->transStatus() !== false) {
                $db->transRollback();
            }
            log_message('error', 'Order approval failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    /**
     * Cancel order
     */
    public function cancel()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX request required']);
        }

        if (!session('user_id')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not authenticated']);
        }

        $json = $this->request->getJSON(true);
        $orderId = (int)($json['order_id'] ?? 0);
        $reason = (string)($json['reason'] ?? 'No reason provided');

        if (!$orderId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid order ID']);
        }

        try {
            $this->orderModel->cancelOrder($orderId, session('user_id'), $reason);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Order cancelled',
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Order cancellation failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get pending orders for admin approval
     */
    public function pending()
    {
        if (!session('user_id')) {
            return redirect()->to('/login');
        }

        try {
            $pendingOrders = $this->orderModel->getPending();

            // Get items for each order
            foreach ($pendingOrders as &$order) {
                $order = $this->orderModel->getWithItems($order['id']);
            }

            $data = [
                'orders' => $pendingOrders,
                'title'  => 'Pending Orders',
            ];

            return view('order/pending', $data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get pending orders: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load pending orders');
        }
    }
}
