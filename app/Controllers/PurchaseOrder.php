<?php

namespace App\Controllers;

use App\Models\PurchaseOrderModel;
use App\Models\AuditLogModel;

class PurchaseOrder extends BaseController
{
    protected $poModel;
    protected $auditModel;

    public function __construct()
    {
        $this->poModel = new PurchaseOrderModel();
        $this->auditModel = new AuditLogModel();
    }

    /**
     * List all POs for admin review / supplier to action
     */
    public function index()
    {
        if (!session('user_id')) {
            return redirect()->to('/login');
        }

        $db = db_connect();
        $roles = $this->getUserRoles();
        $isAdmin = in_array('central_admin', $roles);

        if (!$isAdmin) {
            return $this->response->setStatusCode(403)->setBody('Access denied');
        }

        $data = [
            'purchaseOrders' => $this->poModel->findAll(),
            'title' => 'Purchase Orders',
        ];

        return view('purchase_order/admin_dashboard', $data);
    }

    /**
     * Supplier confirms/accepts a PO (status becomes SUPPLIER_CONFIRMED)
     * POST: po_id, supplier_id, confirmation_notes (optional)
     */
    public function supplierAccept()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX request required']);
        }

        $poId = (int)$this->request->getJSON()->po_id ?? 0;
        $supplierId = (int)$this->request->getJSON()->supplier_id ?? 0;
        $notes = $this->request->getJSON()->notes ?? null;

        if (!$poId || !$supplierId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid PO or supplier ID']);
        }

        try {
            $db = db_connect();

            $po = $this->poModel->find($poId);
            if (!$po || $po['supplier_id'] != $supplierId) {
                throw new \Exception('PO not found or supplier mismatch');
            }

            $db->table('purchase_orders')->update(
                ['status' => 'SUPPLIER_CONFIRMED'],
                ['id' => $poId]
            );

            $this->auditModel->log(
                'supplier_confirmed_po',
                'Supplier ' . $supplierId . ' confirmed PO ' . $poId . '. Notes: ' . $notes,
                null,
                'supplier'
            );

            // Notify admin and manager
            $this->notifyOnSupplierAction($po, 'CONFIRMED', $notes);

            return $this->response->setJSON(['success' => true, 'message' => 'PO confirmed by supplier']);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    /**
     * Supplier requests changes on PO (status becomes SUPPLIER_REQUESTED_CHANGES)
     * POST: po_id, supplier_id, request_reason
     */
    public function supplierRequestChanges()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX request required']);
        }

        $poId = (int)$this->request->getJSON()->po_id ?? 0;
        $supplierId = (int)$this->request->getJSON()->supplier_id ?? 0;
        $reason = $this->request->getJSON()->reason ?? '';

        if (!$poId || !$supplierId || !$reason) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing required fields']);
        }

        try {
            $db = db_connect();
            $po = $this->poModel->find($poId);
            if (!$po) {
                throw new \Exception('PO not found');
            }

            $db->table('purchase_orders')->update(
                ['status' => 'SUPPLIER_REQUESTED_CHANGES'],
                ['id' => $poId]
            );

            $this->auditModel->log(
                'supplier_requested_changes_on_po',
                'PO ' . $poId . ' reason: ' . $reason,
                null,
                'supplier'
            );

            $this->notifyOnSupplierAction($po, 'REQUEST_CHANGES', $reason);

            return $this->response->setJSON(['success' => true, 'message' => 'Change request submitted']);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    /**
     * Supplier declines PO (status becomes SUPPLIER_DECLINED)
     * POST: po_id, supplier_id, reason
     */
    public function supplierDecline()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX request required']);
        }

        $poId = (int)$this->request->getJSON()->po_id ?? 0;
        $supplierId = (int)$this->request->getJSON()->supplier_id ?? 0;
        $reason = $this->request->getJSON()->reason ?? '';

        if (!$poId || !$supplierId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid PO or supplier']);
        }

        try {
            $db = db_connect();
            $po = $this->poModel->find($poId);
            if (!$po) {
                throw new \Exception('PO not found');
            }

            $db->table('purchase_orders')->update(
                ['status' => 'SUPPLIER_DECLINED'],
                ['id' => $poId]
            );

            $this->auditModel->log(
                'supplier_declined_po',
                'PO ' . $poId . ' reason: ' . $reason,
                null,
                'supplier'
            );

            $this->notifyOnSupplierAction($po, 'DECLINED', $reason);

            return $this->response->setJSON(['success' => true, 'message' => 'PO declined. Admin notified to reassign supplier']);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    /**
     * Supplier ships items and provides tracking number
     * POST: po_id, supplier_id, tracking_number
     */
    public function supplierShip()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX request required']);
        }

        $poId = (int)$this->request->getJSON()->po_id ?? 0;
        $trackingNumber = $this->request->getJSON()->tracking_number ?? '';

        if (!$poId || !$trackingNumber) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid PO or tracking number']);
        }

        try {
            $db = db_connect();
            $po = $this->poModel->find($poId);
            if (!$po) {
                throw new \Exception('PO not found');
            }

            $db->table('purchase_orders')->update(
                ['status' => 'SHIPPED', 'tracking_number' => $trackingNumber],
                ['id' => $poId]
            );

            $this->auditModel->log(
                'po_shipped',
                'PO ' . $poId . ' shipped with tracking: ' . $trackingNumber,
                session('user_id'),
                'supplier'
            );

            $this->notifyOnShipment($po, $trackingNumber);

            return $this->response->setJSON(['success' => true, 'message' => 'Shipment recorded']);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    /**
     * Admin marks PO as DELIVERED and updates inventory
     * POST: po_id, quantity_received (per item or null=use ordered quantity)
     */
    public function markDelivered()
    {
        if (!session('user_id')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not authenticated']);
        }

        $poId = (int)$this->request->getJSON()->po_id ?? 0;

        if (!$poId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid PO']);
        }

        try {
            $db = db_connect();
            $db->transStart();

            $po = $this->poModel->find($poId);
            if (!$po) {
                throw new \Exception('PO not found');
            }

            // Get supply request to find branch
            $sr = $db->table('supply_requests')->find($po['supply_request_id']);
            if (!$sr) {
                throw new \Exception('Supply request not found');
            }

            // Get PO items
            $items = $db->table('purchase_order_items')->where('purchase_order_id', $poId)->get()->getResultArray();

            // Update inventory for each item
            foreach ($items as $item) {
                $qty = $item['quantity'];
                // Update or insert branch stock
                $existing = $db->table('branch_stocks')
                              ->where('branch_id', $sr['branch_id'])
                              ->where('item_id', $item['item_id'])
                              ->get()->getRowArray();
                if ($existing) {
                    $db->table('branch_stocks')->update(
                        ['quantity' => $existing['quantity'] + $qty],
                        ['id' => $existing['id']]
                    );
                } else {
                    $db->table('branch_stocks')->insert([
                        'branch_id' => $sr['branch_id'],
                        'item_id' => $item['item_id'],
                        'quantity' => $qty,
                    ]);
                }
            }

            // Update PO status
            $db->table('purchase_orders')->update(
                ['status' => 'DELIVERED'],
                ['id' => $poId]
            );

            $this->auditModel->log(
                'po_delivered_inventory_updated',
                'PO ' . $poId . ' delivered. Inventory updated for branch ' . $sr['branch_id'],
                session('user_id'),
                'admin'
            );

            $this->notifyOnDelivery($po, $sr);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            return $this->response->setJSON(['success' => true, 'message' => 'PO marked as delivered, inventory updated']);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    // ==================== NOTIFICATION HELPERS ====================

    private function notifyOnSupplierAction($po, $action, $details)
    {
        $db = db_connect();
        // Notify admin
        $admin = $db->table('users')->where('id', $po['created_by'])->get()->getRowArray();
        if ($admin) {
            $db->table('notifications')->insert([
                'recipient_id' => $admin['id'],
                'type' => 'supplier_action_on_po',
                'title' => 'Supplier Action on PO #' . $po['po_number'],
                'message' => 'Supplier action: ' . $action . '. Details: ' . $details,
                'related_id' => $po['id'],
                'related_type' => 'purchase_order',
            ]);
        }
    }

    private function notifyOnShipment($po, $trackingNumber)
    {
        $db = db_connect();
        $sr = $db->table('supply_requests')->find($po['supply_request_id']);
        if ($sr) {
            $manager = $db->table('users')->where('branch_id', $sr['branch_id'])->get()->getRowArray();
            if ($manager) {
                $db->table('notifications')->insert([
                    'recipient_id' => $manager['id'],
                    'type' => 'po_shipped',
                    'title' => 'PO #' . $po['po_number'] . ' Shipped',
                    'message' => 'Your PO has been shipped with tracking: ' . $trackingNumber,
                    'related_id' => $po['id'],
                    'related_type' => 'purchase_order',
                ]);
            }
        }
    }

    private function notifyOnDelivery($po, $sr)
    {
        $db = db_connect();
        // Notify manager and admin
        $manager = $db->table('users')->where('branch_id', $sr['branch_id'])->select('id')->get()->getRowArray();
        $admin = $db->table('users')->where('id', $po['created_by'])->get()->getRowArray();

        if ($manager) {
            $db->table('notifications')->insert([
                'recipient_id' => $manager['id'],
                'type' => 'po_delivered',
                'title' => 'PO #' . $po['po_number'] . ' Delivered',
                'message' => 'Your order has arrived and inventory has been updated.',
                'related_id' => $po['id'],
                'related_type' => 'purchase_order',
            ]);
        }

        if ($admin) {
            $db->table('notifications')->insert([
                'recipient_id' => $admin['id'],
                'type' => 'po_delivered',
                'title' => 'PO #' . $po['po_number'] . ' Delivered',
                'message' => 'Purchase order delivered and inventory updated for branch ' . $sr['branch_id'],
                'related_id' => $po['id'],
                'related_type' => 'purchase_order',
            ]);
        }
    }

    private function getUserRoles()
    {
        $db = db_connect();
        $roles = $db->table('user_roles')
                    ->select('roles.name')
                    ->join('roles', 'roles.id = user_roles.role_id')
                    ->where('user_id', session('user_id'))
                    ->get()
                    ->getResultArray();
        return array_column($roles, 'name');
    }
}
