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

    private function getSupplierIdForCurrentUser(): ?int
    {
        $userId = (int) session('user_id');
        if ($userId <= 0) {
            return null;
        }

        $db = db_connect();
        if (! $db->tableExists('user_suppliers')) {
            return null;
        }

        $row = $db->table('user_suppliers')->select('supplier_id')->where('user_id', $userId)->get()->getRowArray();
        return $row ? (int) $row['supplier_id'] : null;
    }

    private function logActivity(string $action, array $details = []): void
    {
        $db = db_connect();
        if (! $db->tableExists('activity_logs')) {
            return;
        }

        $db->table('activity_logs')->insert([
            'user_id' => session('user_id') ?: null,
            'action' => $action,
            'details' => json_encode($details),
            'created_at' => gmdate('Y-m-d H:i:s'),
        ]);
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

    public function supplierPortal()
    {
        if (!session('user_id')) {
            return redirect()->to(site_url('login'));
        }

        $roles = $this->getUserRoles();
        if (!in_array('supplier', $roles, true)) {
            return $this->response->setStatusCode(403)->setBody('Access denied');
        }

        $supplierId = $this->getSupplierIdForCurrentUser();
        if (!$supplierId) {
            return $this->response->setStatusCode(403)->setBody('Supplier account not linked. Please contact administrator.');
        }

        return view('purchase_order/supplier_dashboard', ['title' => 'Supplier Dashboard']);
    }

    public function supplierList()
    {
        if (!session('user_id')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not authenticated']);
        }

        $roles = $this->getUserRoles();
        if (!in_array('supplier', $roles, true)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
        }

        $supplierId = $this->getSupplierIdForCurrentUser();
        if (!$supplierId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Supplier account not linked']);
        }

        $db = db_connect();
        $pos = $db->table('purchase_orders')
            ->select('id, po_number, total_items, total_amount, status, created_at, tracking_number')
            ->where('supplier_id', $supplierId)
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();

        $counts = [
            'awaiting_response' => 0,
            'confirmed' => 0,
            'shipped' => 0,
            'declined' => 0,
        ];

        foreach ($pos as $po) {
            if ($po['status'] === 'PO_CREATED') {
                $counts['awaiting_response']++;
            } elseif ($po['status'] === 'SUPPLIER_CONFIRMED') {
                $counts['confirmed']++;
            } elseif ($po['status'] === 'SHIPPED') {
                $counts['shipped']++;
            } elseif ($po['status'] === 'SUPPLIER_DECLINED') {
                $counts['declined']++;
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'supplier_id' => $supplierId,
            'counts' => $counts,
            'purchase_orders' => $pos,
        ]);
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

        $json = $this->request->getJSON(true);
        $poId = (int) ($json['po_id'] ?? 0);
        $notes = $json['notes'] ?? null;

        $supplierId = $this->getSupplierIdForCurrentUser();

        if (!$poId || !$supplierId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid PO or supplier']);
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
                session('user_id'),
                'supplier'
            );

            $this->logActivity('po_supplier_confirm', [
                'po_id' => $poId,
                'supplier_id' => $supplierId,
                'notes' => $notes,
            ]);

            // Notify admin and manager
            $this->notifyOnSupplierAction($po, 'CONFIRMED', $notes);

            // Create a logistics delivery row for this PO (once)
            if ($db->tableExists('deliveries') && $db->fieldExists('current_location', 'deliveries')) {
                $token = 'PO#' . $poId;
                $existingDelivery = $db->table('deliveries')
                    ->select('id')
                    ->where('type', 'PO')
                    ->where('current_location', $token)
                    ->get()->getRowArray();

                if (! $existingDelivery) {
                    $now = gmdate('Y-m-d H:i:s');
                    $db->table('deliveries')->insert([
                        'order_id' => null,
                        'transfer_id' => null,
                        'type' => 'PO',
                        'driver_name' => null,
                        'vehicle' => null,
                        'route' => null,
                        'status' => 'scheduled',
                        'scheduled_at' => null,
                        'current_location' => $token,
                        'created_by' => session('user_id') ?: null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

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
        $reason = $this->request->getJSON()->reason ?? '';

        $supplierId = $this->getSupplierIdForCurrentUser();

        if (!$poId || !$supplierId || !$reason) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing required fields']);
        }

        try {
            $db = db_connect();
            $po = $this->poModel->find($poId);
            if (!$po || $po['supplier_id'] != $supplierId) {
                throw new \Exception('PO not found or supplier mismatch');
            }

            $db->table('purchase_orders')->update(
                ['status' => 'SUPPLIER_REQUESTED_CHANGES'],
                ['id' => $poId]
            );

            $this->auditModel->log(
                'supplier_requested_changes_on_po',
                'PO ' . $poId . ' reason: ' . $reason,
                session('user_id'),
                'supplier'
            );

            $this->logActivity('po_supplier_request_changes', [
                'po_id' => $poId,
                'supplier_id' => $supplierId,
                'reason' => $reason,
            ]);

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
        $reason = $this->request->getJSON()->reason ?? '';

        $supplierId = $this->getSupplierIdForCurrentUser();

        if (!$poId || !$supplierId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid PO or supplier']);
        }

        try {
            $db = db_connect();
            $po = $this->poModel->find($poId);
            if (!$po || (int)($po['supplier_id'] ?? 0) !== (int)$supplierId) {
                throw new \Exception('PO not found or supplier mismatch');
            }

            $db->table('purchase_orders')->update(
                ['status' => 'SUPPLIER_DECLINED'],
                ['id' => $poId]
            );

            $this->auditModel->log(
                'supplier_declined_po',
                'PO ' . $poId . ' reason: ' . $reason,
                session('user_id'),
                'supplier'
            );

            $this->logActivity('po_supplier_decline', [
                'po_id' => $poId,
                'supplier_id' => $supplierId,
                'reason' => $reason,
            ]);

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

        $json = $this->request->getJSON(true);
        $poId = (int) ($json['po_id'] ?? 0);
        $trackingNumber = (string) ($json['tracking_number'] ?? '');

        if (!$poId || !$trackingNumber) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid PO or tracking number']);
        }

        try {
            $db = db_connect();
            $supplierId = $this->getSupplierIdForCurrentUser();
            $po = $this->poModel->find($poId);
            if (!$po || !$supplierId || (int)($po['supplier_id'] ?? 0) !== (int)$supplierId) {
                throw new \Exception('PO not found or supplier mismatch');
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

            $this->logActivity('po_supplier_ship', [
                'po_id' => $poId,
                'supplier_id' => $supplierId,
                'tracking_number' => $trackingNumber,
            ]);

            $this->notifyOnShipment($po, $trackingNumber);

            // Ensure there is a logistics delivery row for this PO (once)
            if ($db->tableExists('deliveries') && $db->fieldExists('current_location', 'deliveries')) {
                $token = 'PO#' . $poId;
                $existingDelivery = $db->table('deliveries')
                    ->select('id')
                    ->where('type', 'PO')
                    ->where('current_location', $token)
                    ->get()->getRowArray();

                if (! $existingDelivery) {
                    $now = gmdate('Y-m-d H:i:s');
                    $db->table('deliveries')->insert([
                        'order_id' => null,
                        'transfer_id' => null,
                        'type' => 'PO',
                        'driver_name' => null,
                        'vehicle' => null,
                        'route' => null,
                        'status' => 'scheduled',
                        'scheduled_at' => null,
                        'current_location' => $token,
                        'created_by' => session('user_id') ?: null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

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

        $roles = $this->getUserRoles();
        if (!in_array('central_admin', $roles, true) && !in_array('system_admin', $roles, true)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
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

            $branchId = null;
            $sr = null;
            if (!empty($po['supply_request_id'])) {
                $sr = $db->table('supply_requests')
                    ->where('id', $po['supply_request_id'])
                    ->get()
                    ->getRowArray();
                if (!$sr) {
                    throw new \Exception('Supply request not found');
                }
                $branchId = (int) ($sr['branch_id'] ?? 0);
            }

            if (!$branchId) {
                $branchId = (int) ($po['branch_id'] ?? 0);
            }

            if (!$branchId) {
                throw new \Exception('Branch not found for this PO');
            }

            // Get PO items
            $items = $db->table('purchase_order_items')->where('purchase_order_id', $poId)->get()->getResultArray();

            // Update inventory for each item
            foreach ($items as $item) {
                $qty = (int) ($item['quantity'] ?? 0);
                $itemId = (int) ($item['item_id'] ?? 0);
                if ($qty <= 0 || $itemId <= 0) {
                    continue;
                }

                // IMPORTANT: branch_stocks is treated as a movement table in this app (inventory sums quantity).
                // So we insert a new positive movement row rather than overwriting an existing row.
                $db->table('branch_stocks')->insert([
                    'branch_id'   => (int) $branchId,
                    'item_id'     => $itemId,
                    'quantity'    => $qty,
                    'expiry_date' => null,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
            }

            // Update PO status
            $db->table('purchase_orders')->update(
                ['status' => 'DELIVERED'],
                ['id' => $poId]
            );

            // Mark related supply request as fulfilled (end of this workflow)
            if (!empty($po['supply_request_id'])) {
                $db->table('supply_requests')->update(
                    ['status' => 'Fulfilled', 'updated_at' => date('Y-m-d H:i:s')],
                    ['id' => (int) $po['supply_request_id']]
                );
            }

            $this->logActivity('po_delivered_inventory_updated', [
                'po_id' => $poId,
                'po_number' => $po['po_number'] ?? null,
                'supply_request_id' => $po['supply_request_id'] ?? null,
                'branch_id' => (int) $branchId,
                'items' => array_map(function($it) {
                    return [
                        'item_id' => (int) ($it['item_id'] ?? 0),
                        'quantity' => (int) ($it['quantity'] ?? 0),
                    ];
                }, $items),
            ]);

            $this->auditModel->log(
                'po_delivered_inventory_updated',
                'PO ' . $poId . ' delivered. Inventory updated for branch ' . $branchId,
                session('user_id'),
                'admin'
            );

            if ($sr) {
                $this->notifyOnDelivery($po, $sr);
            }

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
        $sr = $db->table('supply_requests')
                 ->where('id', $po['supply_request_id'])
                 ->get()
                 ->getRowArray();
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
