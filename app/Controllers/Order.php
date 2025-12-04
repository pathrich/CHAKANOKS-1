<?php

namespace App\Controllers;

use App\Models\OrderModel;

class Order extends BaseController
{
    protected $orderModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
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

        // Get user's branch
        $user = $db->table('users')
                   ->select('branch_id')
                   ->find(session('user_id'));

        if (!$user || !$user['branch_id']) {
            return $this->response->setStatusCode(403)->setBody('User has no assigned branch');
        }

        // Get orders for this branch
        $orders = $this->orderModel->getByBranch($user['branch_id']);

        // Get detailed items for each order
        foreach ($orders as &$order) {
            $order = $this->orderModel->getWithItems($order['id']);
        }

        $data = [
            'orders'   => $orders,
            'branchId' => $user['branch_id'],
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

        // Get user's branch
        $user = $db->table('users')
                   ->select('branch_id')
                   ->find(session('user_id'));

        if (!$user || !$user['branch_id']) {
            return $this->response->setStatusCode(403)->setBody('User has no assigned branch');
        }

        // Get all items
        $items = $db->table('items')
                    ->select('items.id, items.name, items.sku, item_categories.name as category')
                    ->join('item_categories', 'item_categories.id = items.category_id', 'LEFT')
                    ->get()
                    ->getResultArray();

        $data = [
            'items'    => $items,
            'branchId' => $user['branch_id'],
            'title'    => 'Create New Order',
        ];

        return view('order/create', $data);
    }

    /**
     * Store new order
     * Expected POST data:
     * - items: array of ['item_id' => int, 'quantity' => int, 'unit_price' => float, 'notes' => string (optional)]
     * - notes: string (optional)
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
                   ->find(session('user_id'));

        if (!$user || !$user['branch_id']) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'User has no assigned branch']);
        }

        // Validate input
        $items = $this->request->getJSON()->items ?? [];
        $notes = $this->request->getJSON()->notes ?? null;

        if (empty($items)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'No items provided']);
        }

        // Validate each item
        foreach ($items as $item) {
            if (!isset($item->item_id, $item->quantity, $item->unit_price)) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid item format']);
            }
            if ($item->quantity <= 0 || $item->unit_price < 0) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid quantity or price']);
            }
        }

        try {
            // Convert JSON items to array
            $itemsArray = [];
            foreach ($items as $item) {
                $itemsArray[] = [
                    'item_id'    => (int)$item->item_id,
                    'quantity'   => (int)$item->quantity,
                    'unit_price' => (float)$item->unit_price,
                    'notes'      => $item->notes ?? null,
                ];
            }

            // Create order
            $orderId = $this->orderModel->createWithItems(
                $user['branch_id'],
                session('user_id'),
                $itemsArray,
                $notes
            );

            $order = $this->orderModel->find($orderId);

            return $this->response->setJSON([
                'success'     => true,
                'orderId'     => $orderId,
                'orderNumber' => $order['order_number'],
                'message'     => 'Order created successfully',
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Order creation failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to create order']);
        }
    }

    /**
     * Submit order for approval
     */
    public function submit()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX request required']);
        }

        if (!session('user_id')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not authenticated']);
        }

        $orderId = (int)$this->request->getJSON()->order_id ?? 0;

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
            log_message('error', 'Order submission failed: ' . $e->getMessage());
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

        $orderId = (int)$this->request->getJSON()->order_id ?? 0;

        if (!$orderId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid order ID']);
        }

        try {
            $this->orderModel->approveOrder($orderId, session('user_id'));

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Order approved',
            ]);
        } catch (\Exception $e) {
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

        $orderId = (int)$this->request->getJSON()->order_id ?? 0;
        $reason = $this->request->getJSON()->reason ?? 'No reason provided';

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
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not authenticated']);
        }

        try {
            $pendingOrders = $this->orderModel->getPending();

            // Get items for each order
            foreach ($pendingOrders as &$order) {
                $order = $this->orderModel->getWithItems($order['id']);
            }

            return $this->response->setJSON([
                'orders' => $pendingOrders,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get pending orders: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to get pending orders']);
        }
    }
}
