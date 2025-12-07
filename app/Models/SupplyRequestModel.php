<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplyRequestModel extends Model
{
    protected $table            = 'supply_requests';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'branch_id',
        'requested_by',
        'status',
        'total_items',
        'notes',
        'approved_by',
        'approved_at',
        'rejected_reason',
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get pending supply requests for admin dashboard
     */
    public function getPending()
    {
        return $this->where('status', 'Pending')
                    ->select('supply_requests.*, users.full_name as requester_name, branches.name as branch_name')
                    ->join('users', 'users.id = supply_requests.requested_by')
                    ->join('branches', 'branches.id = supply_requests.branch_id')
                    ->orderBy('supply_requests.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get supply requests by branch
     */
    public function getByBranch($branchId)
    {
        return $this->where('branch_id', $branchId)
                    ->select('supply_requests.*, users.full_name as requester_name')
                    ->join('users', 'users.id = supply_requests.requested_by')
                    ->orderBy('supply_requests.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get request with line items
     */
    public function getWithItems($requestId)
    {
        $request = $this->find($requestId);
        if (!$request) {
            return null;
        }

        $request['items'] = $this->db->table('supply_request_items')
                                      ->select('supply_request_items.*, items.name, items.sku')
                                      ->join('items', 'items.id = supply_request_items.item_id')
                                      ->where('supply_request_id', $requestId)
                                      ->get()
                                      ->getResultArray();

        return $request;
    }

    /**
     * Create supply request with items
     */
    public function createWithItems($branchId, $requestedBy, $items, $notes = null)
    {
        $db = $this->db;
        $db->transBegin();

        try {
            // Insert main request
            $requestData = [
                'branch_id'   => $branchId,
                'requested_by' => $requestedBy,
                'status'      => 'Pending',
                'total_items' => count($items),
                'notes'       => $notes,
            ];

            $db->table('supply_requests')->insert($requestData);
            $requestId = $db->insertID();

            // Insert line items
            $lineItems = [];
            foreach ($items as $item) {
                $lineItems[] = [
                    'supply_request_id'   => $requestId,
                    'item_id'             => $item['item_id'],
                    'quantity_requested'  => $item['quantity'],
                    'notes'               => $item['notes'] ?? null,
                ];
            }

            if (!empty($lineItems)) {
                $db->table('supply_request_items')->insertBatch($lineItems);
            }

            // Log activity
            $db->table('activity_logs')->insert([
                'user_id'    => $requestedBy,
                'action'     => 'supply_request_submitted',
                'details'    => "Supply request #$requestId submitted for branch $branchId",
            ]);

            $db->transCommit();
            return $requestId;
        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Approve supply request and notify branch manager
     */
    public function approveRequest($requestId, $approvedBy, $approvalNotes = null)
    {
        $db = $this->db;
        $db->transBegin();

        try {
            // Update request status to Approved (we will create a PO next)
            $db->table('supply_requests')->update(
                [
                    'status'      => 'Approved',
                    'approved_by' => $approvedBy,
                    'approved_at' => date('Y-m-d H:i:s'),
                ],
                ['id' => $requestId]
            );

            // Get request details
            $request = $this->getWithItems($requestId);
            if (!$request) {
                throw new \Exception('Supply request not found');
            }

            // Log activity
            $db->table('activity_logs')->insert([
                'user_id'    => $approvedBy,
                'action'     => 'supply_request_approved',
                'details'    => "Supply request #$requestId approved",
            ]);

            // Create Purchase Order for this approved request
            $poModel = new \App\Models\PurchaseOrderModel();

            // Choose a supplier: for now pick the first available supplier
            $supplier = $db->table('suppliers')->orderBy('id', 'ASC')->get()->getRowArray();
            $supplierId = $supplier['id'] ?? null;

            // Prepare items for PO (unit_price unknown => 0)
            $poItems = [];
            foreach ($request['items'] as $it) {
                $poItems[] = [
                    'item_id' => $it['item_id'],
                    'quantity' => $it['quantity_requested'],
                    'unit_price' => 0.00,
                    'notes' => $it['notes'] ?? null,
                ];
            }

            $po = $poModel->createFromSupplyRequest($requestId, $supplierId, $poItems, $approvedBy, $approvalNotes);

            // Notify branch manager and franchise and supplier
            $this->notifyBranchManager($request, $approvedBy, $po);
            $this->notifySupplier($supplierId, $po);
            $this->notifyFranchise($request, $po);

            // Log audit
            $audit = new \App\Models\AuditLogModel();
            $audit->log('purchase_order_created', json_encode(['po_id' => $po['id'], 'supply_request_id' => $requestId]), $approvedBy, 'admin');

            $db->transCommit();
            return $po;
        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Reject supply request
     */
    public function rejectRequest($requestId, $rejectedBy, $reason)
    {
        $db = $this->db;
        $db->transBegin();

        try {
            // Update request status
            $db->table('supply_requests')->update(
                [
                    'status'           => 'Rejected',
                    'approved_by'      => $rejectedBy,
                    'approved_at'      => date('Y-m-d H:i:s'),
                    'rejected_reason'  => $reason,
                ],
                ['id' => $requestId]
            );

            // Log activity
            $db->table('activity_logs')->insert([
                'user_id'    => $rejectedBy,
                'action'     => 'supply_request_rejected',
                'details'    => "Supply request #$requestId rejected: $reason",
            ]);

            $db->transCommit();
            return $requestId;
        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Notify branch manager of approval
     */
    private function notifyBranchManager($request, $approvedBy)
    {
        $db = $this->db;

        // Get branch manager
        $manager = $db->table('users')
                      ->where('branch_id', $request['branch_id'])
                      ->where('id', '!=', $request['requested_by'])
                      ->select('id')
                      ->first();

        if (!$manager) {
            return;
        }

        // Get approver name
        $approver = $db->table('users')->select('full_name')->find($approvedBy);

        // Create notification
        $message = sprintf(
            "Supply request #%d from %s branch has been approved by %s. %d items requested. Please process accordingly.",
            $request['id'],
            $request['branch_name'] ?? 'Your',
            $approver['full_name'] ?? 'Admin',
            $request['total_items']
        );

        $db->table('notifications')->insert([
            'recipient_id'  => $manager['id'],
            'type'          => 'supply_request_approved',
            'title'         => "Supply Request #" . $request['id'] . " Approved",
            'message'       => $message,
            'related_id'    => $request['id'],
            'related_type'  => 'supply_request',
        ]);
    }

    private function notifySupplier($supplierId, $po)
    {
        if (!$supplierId || !$po) return;

        $db = $this->db;
        $supplier = $db->table('suppliers')->find($supplierId);
        if (!$supplier) return;

        $message = sprintf("New Purchase Order #%s created. Total items: %d. Please confirm or request changes.", $po['po_number'], $po['total_items']);

        // Create a notification record
        $db->table('notifications')->insert([
            'recipient_id' => null,
            'type' => 'purchase_order_created',
            'title' => "PO #" . $po['po_number'],
            'message' => $message,
            'related_id' => $po['id'],
            'related_type' => 'purchase_order',
        ]);
    }

    private function notifyFranchise($request, $po)
    {
        $db = $this->db;
        // If the request referenced a franchise as destination, try to notify - using branch's manager
        $branchManager = $db->table('users')->where('branch_id', $request['branch_id'])->select('id')->first();
        if ($branchManager) {
            $db->table('notifications')->insert([
                'recipient_id' => $branchManager['id'],
                'type' => 'purchase_order_created',
                'title' => "PO #" . $po['po_number'] . " Created",
                'message' => "Purchase Order " . $po['po_number'] . " has been created for your request.",
                'related_id' => $po['id'],
                'related_type' => 'purchase_order',
            ]);
        }
    }
}
