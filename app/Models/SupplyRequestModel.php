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
        'preferred_supplier_id',
        'required_delivery_date',
        'branch_approved_by',
        'branch_approved_at',
        'branch_rejected_reason',
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
        return $this->where('supply_requests.status', 'Pending Central Approval')
                    ->select('supply_requests.*, users.full_name as requester_name, branches.name as branch_name')
                    ->join('users', 'users.id = supply_requests.requested_by')
                    ->join('branches', 'branches.id = supply_requests.branch_id')
                    ->orderBy('supply_requests.created_at', 'DESC')
                    ->findAll();
    }

    public function getPendingBranchApproval(int $branchId): array
    {
        return $this->where('supply_requests.branch_id', $branchId)
            ->where('supply_requests.status', 'Pending Branch Approval')
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
        // Fetch main request with branch and requester info
        $request = $this->select('supply_requests.*, users.full_name as requester_name, branches.name as branch_name')
                        ->join('users', 'users.id = supply_requests.requested_by', 'left')
                        ->join('branches', 'branches.id = supply_requests.branch_id', 'left')
                        ->where('supply_requests.id', $requestId)
                        ->first();

        if (!$request) {
            return null;
        }

        // Attach line items
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
                'status'      => 'Pending Branch Approval',
                'total_items' => count($items),
                'notes'       => $notes,
            ];

            $inserted = $db->table('supply_requests')->insert($requestData);
            if (! $inserted) {
                $dbError = $db->error();
                throw new \RuntimeException('Failed to insert supply request: ' . ($dbError['message'] ?? 'unknown DB error'));
            }

            $requestId = (int) $db->insertID();
            if ($requestId <= 0) {
                throw new \RuntimeException('Failed to obtain supply request ID after insert');
            }

            // Insert line items (support both array and stdClass from JSON)
            $lineItems = [];
            foreach ($items as $item) {
                if (is_array($item)) {
                    $itemId   = $item['item_id'] ?? null;
                    $quantity = $item['quantity'] ?? null;
                    $notes    = $item['notes'] ?? null;
                } else {
                    // stdClass or other object
                    $itemId   = $item->item_id ?? null;
                    $quantity = $item->quantity ?? null;
                    $notes    = $item->notes ?? null;
                }

                if (! $itemId || ! $quantity) {
                    continue; // skip invalid rows silently
                }

                $lineItems[] = [
                    'supply_request_id'   => $requestId,
                    'item_id'             => $itemId,
                    'quantity_requested'  => $quantity,
                    'notes'               => $notes,
                ];
            }

            if (!empty($lineItems)) {
                $insertedItems = $db->table('supply_request_items')->insertBatch($lineItems);
                if (! $insertedItems) {
                    $dbError = $db->error();
                    throw new \RuntimeException('Failed to insert supply request items: ' . ($dbError['message'] ?? 'unknown DB error'));
                }
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
            $current = $db->table('supply_requests')->where('id', $requestId)->get()->getRowArray();
            if (!$current) {
                throw new \Exception('Supply request not found');
            }

            if (($current['status'] ?? '') !== 'Pending Central Approval') {
                throw new \Exception('Only requests pending central approval can be approved');
            }

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

            // Prevent duplicate PO creation if approve is clicked more than once
            $existingPo = $poModel->findBySupplyRequestId((int) $requestId);
            if ($existingPo) {
                $db->transCommit();
                return $existingPo;
            }

            $supplierId = $request['preferred_supplier_id'] ?? null;
            if (!$supplierId) {
                $supplier = $db->table('suppliers')->orderBy('id', 'ASC')->get()->getRowArray();
                $supplierId = $supplier['id'] ?? null;
            }

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

            $po = $poModel->createFromSupplyRequest((int) $requestId, $supplierId ? (int) $supplierId : null, $poItems, (int) $approvedBy, $approvalNotes);

            // Notify branch manager and franchise and supplier
            $this->notifyBranchManager($request, (int) $approvedBy);
            $this->notifySupplier($supplierId ? (int) $supplierId : null, $po);
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
            $current = $db->table('supply_requests')->where('id', $requestId)->get()->getRowArray();
            if (!$current) {
                throw new \Exception('Supply request not found');
            }

            if (($current['status'] ?? '') !== 'Pending Central Approval') {
                throw new \Exception('Only requests pending central approval can be rejected');
            }

            // Update request status
            $db->table('supply_requests')->update(
                [
                    'status'           => 'Rejected By Central',
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

    public function branchApproveRequest(int $requestId, int $branchManagerId, ?string $notes = null): void
    {
        $db = $this->db;
        $db->transBegin();

        try {
            $current = $db->table('supply_requests')->where('id', $requestId)->get()->getRowArray();
            if (!$current) {
                throw new \Exception('Supply request not found');
            }

            if (($current['status'] ?? '') !== 'Pending Branch Approval') {
                throw new \Exception('Only requests pending branch approval can be approved');
            }

            $payload = [
                'status' => 'Pending Central Approval',
                'branch_approved_by' => $branchManagerId,
                'branch_approved_at' => date('Y-m-d H:i:s'),
                'branch_rejected_reason' => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if ($notes !== null) {
                $payload['notes'] = $notes;
            }

            $db->table('supply_requests')->update($payload, ['id' => $requestId]);

            if ($db->tableExists('activity_logs')) {
                $db->table('activity_logs')->insert([
                    'user_id'    => $branchManagerId,
                    'action'     => 'supply_request_branch_approved',
                    'details'    => json_encode(['request_id' => $requestId]),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $db->transCommit();
        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    public function branchRejectRequest(int $requestId, int $branchManagerId, string $reason): void
    {
        $db = $this->db;
        $db->transBegin();

        try {
            $current = $db->table('supply_requests')->where('id', $requestId)->get()->getRowArray();
            if (!$current) {
                throw new \Exception('Supply request not found');
            }

            if (($current['status'] ?? '') !== 'Pending Branch Approval') {
                throw new \Exception('Only requests pending branch approval can be rejected');
            }

            $db->table('supply_requests')->update(
                [
                    'status' => 'Rejected By Branch',
                    'branch_approved_by' => $branchManagerId,
                    'branch_approved_at' => date('Y-m-d H:i:s'),
                    'branch_rejected_reason' => $reason,
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                ['id' => $requestId]
            );

            if ($db->tableExists('activity_logs')) {
                $db->table('activity_logs')->insert([
                    'user_id'    => $branchManagerId,
                    'action'     => 'supply_request_branch_rejected',
                    'details'    => json_encode(['request_id' => $requestId, 'reason' => $reason]),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $db->transCommit();
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
                      ->get()
                      ->getRowArray();

        if (!$manager) {
            return;
        }

        // Get approver name
        $approver = $db->table('users')
                 ->select('full_name')
                 ->where('id', $approvedBy)
                 ->get()
                 ->getRowArray();

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
        if (!$po) {
            return;
        }

        // If we don't have a supplier, we can't target supplier users.
        if (!$supplierId) {
            return;
        }

        $db = $this->db;

        // Notify supplier-linked users (via user_suppliers pivot) if available
        $recipientIds = [];
        if ($db->tableExists('user_suppliers')) {
            $rows = $db->table('user_suppliers')->select('user_id')->where('supplier_id', (int) $supplierId)->get()->getResultArray();
            $recipientIds = array_map(fn($r) => (int) $r['user_id'], $rows);
        }

        $message = sprintf(
            'New Purchase Order %s created. Total items: %d. Please confirm or request changes.',
            $po['po_number'],
            (int) ($po['total_items'] ?? 0)
        );

        foreach (array_unique($recipientIds) as $uid) {
            if ($uid <= 0) continue;
            $db->table('notifications')->insert([
                'recipient_id' => $uid,
                'type' => 'purchase_order_created',
                'title' => 'PO #' . $po['po_number'],
                'message' => $message,
                'related_id' => $po['id'],
                'related_type' => 'purchase_order',
            ]);
        }
    }

    private function notifyFranchise($request, $po)
    {
        $db = $this->db;
        // If the request referenced a franchise as destination, try to notify - using branch's manager
        $branchManager = $db->table('users')
                        ->where('branch_id', $request['branch_id'])
                        ->select('id')
                        ->get()
                        ->getRowArray();
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
