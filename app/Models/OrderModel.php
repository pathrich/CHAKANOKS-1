<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table            = 'orders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'branch_id',
        'created_by',
        'status',
        'order_number',
        'total_items',
        'total_amount',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get orders by branch
     */
    public function getByBranch($branchId, $status = null)
    {
        $query = $this->where('orders.branch_id', $branchId)
                      ->select('orders.*, users.full_name as created_by_name, branches.name as branch_name')
                      ->join('users', 'users.id = orders.created_by')
                      ->join('branches', 'branches.id = orders.branch_id');

        if ($status) {
            $query->where('orders.status', $status);
        }

        return $query->orderBy('orders.created_at', 'DESC')->findAll();
    }

    /**
     * Get order with items
     */
    public function getWithItems($orderId)
    {
        $order = $this->find($orderId);
        if (!$order) {
            return null;
        }

        $order['items'] = $this->db->table('order_items')
                                    ->select('order_items.*, items.name, items.sku')
                                    ->join('items', 'items.id = order_items.item_id')
                                    ->where('order_id', $orderId)
                                    ->get()
                                    ->getResultArray();

        return $order;
    }

    /**
     * Generate order number
     */
    public function generateOrderNumber($branchId)
    {
        $prefix = 'ORD-' . str_pad($branchId, 2, '0', STR_PAD_LEFT) . '-';
        $lastOrder = $this->where('branch_id', $branchId)
                          ->orderBy('id', 'DESC')
                          ->first();

        $sequence = 1;
        if ($lastOrder) {
            preg_match('/(\d+)$/', $lastOrder['order_number'], $matches);
            if ($matches) {
                $sequence = (int)$matches[1] + 1;
            }
        }

        return $prefix . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Create order with items
     */
    public function createWithItems($branchId, $createdBy, $items, $notes = null)
    {
        $db = $this->db;
        $db->transBegin();

        try {
            // Generate order number
            $orderNumber = $this->generateOrderNumber($branchId);

            // Calculate totals
            $totalItems = count($items);
            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += ($item['quantity'] * $item['unit_price']);
            }

            // Insert main order
            $orderData = [
                'branch_id'    => $branchId,
                'created_by'   => $createdBy,
                'status'       => 'Draft',
                'order_number' => $orderNumber,
                'total_items'  => $totalItems,
                'total_amount' => $totalAmount,
                'notes'        => $notes,
            ];

            $db->table('orders')->insert($orderData);
            $orderId = $db->insertID();

            // Insert line items
            $lineItems = [];
            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $lineItems[] = [
                    'order_id'    => $orderId,
                    'item_id'     => $item['item_id'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'subtotal'    => $subtotal,
                    'notes'       => $item['notes'] ?? null,
                ];
            }

            if (!empty($lineItems)) {
                $db->table('order_items')->insertBatch($lineItems);
            }

            // Log activity
            $db->table('activity_logs')->insert([
                'user_id'    => $createdBy,
                'action'     => 'order_created',
                'details'    => "Order $orderNumber created for branch $branchId",
            ]);

            $db->transCommit();
            return $orderId;
        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Submit order (change from Draft to Pending)
     */
    public function submitOrder($orderId, $submittedBy)
    {
        $db = $this->db;
        $db->transBegin();

        try {
            $order = $this->find($orderId);
            if (!$order) {
                throw new \Exception('Order not found');
            }

            if ($order['status'] !== 'Draft') {
                throw new \Exception('Only draft orders can be submitted');
            }

            // Update status to Pending
            $db->table('orders')->update(
                ['status' => 'Pending'],
                ['id' => $orderId]
            );

            // Log activity
            $db->table('activity_logs')->insert([
                'user_id'    => $submittedBy,
                'action'     => 'order_submitted',
                'details'    => "Order #{$orderId} ({$order['order_number']}) submitted for approval",
            ]);

            $db->transCommit();
            return $orderId;
        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Approve order
     */
    public function approveOrder($orderId, $approvedBy)
    {
        $db = $this->db;
        $db->transBegin();

        try {
            $order = $this->find($orderId);
            if (!$order) {
                throw new \Exception('Order not found');
            }

            // Update status to Approved
            $db->table('orders')->update(
                [
                    'status'      => 'Approved',
                    'approved_by' => $approvedBy,
                    'approved_at' => date('Y-m-d H:i:s'),
                ],
                ['id' => $orderId]
            );

            // Log activity
            $db->table('activity_logs')->insert([
                'user_id'    => $approvedBy,
                'action'     => 'order_approved',
                'details'    => "Order #{$orderId} ({$order['order_number']}) approved",
            ]);

            $db->transCommit();
            return $orderId;
        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Cancel order
     */
    public function cancelOrder($orderId, $cancelledBy, $reason = null)
    {
        $db = $this->db;
        $db->transBegin();

        try {
            $order = $this->find($orderId);
            if (!$order) {
                throw new \Exception('Order not found');
            }

            // Update status to Cancelled
            $db->table('orders')->update(
                ['status' => 'Cancelled'],
                ['id' => $orderId]
            );

            // Log activity
            $db->table('activity_logs')->insert([
                'user_id'    => $cancelledBy,
                'action'     => 'order_cancelled',
                'details'    => "Order #{$orderId} ({$order['order_number']}) cancelled. Reason: $reason",
            ]);

            $db->transCommit();
            return $orderId;
        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Get pending orders for admin
     */
    public function getPending()
    {
        return $this->where('status', 'Pending')
                    ->select('orders.*, users.full_name as created_by_name, branches.name as branch_name')
                    ->join('users', 'users.id = orders.created_by')
                    ->join('branches', 'branches.id = orders.branch_id')
                    ->orderBy('orders.created_at', 'DESC')
                    ->findAll();
    }
}
