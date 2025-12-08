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
                   ->where('id', session('user_id'))
                   ->get()
                   ->getRowArray();

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
                   ->where('id', session('user_id'))
                   ->get()
                   ->getRowArray();

        if (!$user || !$user['branch_id']) {
            return $this->response->setStatusCode(403)->setBody('User has no assigned branch');
        }

        $data = [
            'branchId' => $user['branch_id'],
            'title'    => 'Create New Order',
        ];

        return view('order/create', $data);
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
