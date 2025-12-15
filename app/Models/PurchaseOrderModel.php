<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class PurchaseOrderModel extends Model
{
    protected $table = 'purchase_orders';
    protected $primaryKey = 'id';
    protected $allowedFields = ['order_id','branch_id','supply_request_id','supplier_id','created_by','status','po_number','total_items','total_amount','tracking_number','notes','created_at','updated_at'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function createFromSupplyRequest(int $supplyRequestId, ?int $supplierId, array $items, int $createdBy, ?string $notes = null)
    {
        $db = db_connect();
        $db->transStart();

        // generate PO number
        $poNumber = $this->generatePONumber();

        $totalItems = 0;
        $totalAmount = 0.00;

        foreach ($items as $it) {
            $qty = (int) ($it['quantity'] ?? 0);
            $price = (float) ($it['unit_price'] ?? 0);
            $totalItems += $qty;
            $totalAmount += ($qty * $price);
        }

        $id = $this->insert([
            'supply_request_id' => $supplyRequestId,
            'supplier_id' => $supplierId,
            'created_by' => $createdBy,
            'status' => 'PO_CREATED',
            'po_number' => $poNumber,
            'total_items' => $totalItems,
            'total_amount' => $totalAmount,
            'notes' => $notes,
        ]);

        if (!$id) {
            $db->transRollback();
            throw new Exception('Failed to create purchase order');
        }

        // insert items
        $poi = $db->table('purchase_order_items');
        foreach ($items as $it) {
            $poi->insert([
                'purchase_order_id' => $id,
                'item_id' => $it['item_id'],
                'quantity' => $it['quantity'],
                'unit_price' => $it['unit_price'],
                'subtotal' => $it['quantity'] * $it['unit_price'],
                'notes' => $it['notes'] ?? null,
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new Exception('Transaction failed while creating purchase order');
        }

        return $this->find($id);
    }

    public function findBySupplyRequestId(int $supplyRequestId): ?array
    {
        $row = $this->where('supply_request_id', $supplyRequestId)->orderBy('id', 'DESC')->first();
        return $row ?: null;
    }

    protected function generatePONumber()
    {
        $prefix = 'PO-' . date('Ymd');
        $db = db_connect();
        $count = $db->table($this->table)->like('po_number', $prefix, 'after')->countAllResults();
        return sprintf('%s-%04d', $prefix, $count + 1);
    }
}
