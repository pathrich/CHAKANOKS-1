<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RedirectResponse;

class Inventory extends Controller
{
    public function index()
    {
        $branchId = $this->request->getGet('branch_id') ?? 1;

        $db = db_connect();
        $items = $db->query(
            'SELECT i.id, i.name, i.sku, i.min_stock, i.perishable,
                    COALESCE(SUM(bs.quantity), 0) AS quantity,
                    MIN(bs.expiry_date) AS nearest_expiry
             FROM items i
             LEFT JOIN branch_stocks bs ON bs.item_id = i.id AND bs.branch_id = ?
             GROUP BY i.id, i.name, i.sku, i.min_stock, i.perishable',
            [ $branchId ]
        )->getResult();

        $lowStock = array_filter($items, function($r) { return (int)$r->quantity < (int)$r->min_stock; });
        $expiringSoon = $db->query(
            'SELECT i.name, bs.quantity, bs.expiry_date
             FROM branch_stocks bs
             JOIN items i ON i.id = bs.item_id
             WHERE bs.branch_id = ? AND bs.expiry_date IS NOT NULL AND bs.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)'
            , [ $branchId ]
        )->getResult();

        return view('inventory/index', [
            'items' => $items,
            'lowStock' => $lowStock,
            'expiringSoon' => $expiringSoon,
            'branchId' => $branchId,
        ]);
    }

    public function receive(): RedirectResponse
    {
        $branchId = (int)$this->request->getPost('branch_id');
        $itemId = (int)$this->request->getPost('item_id');
        $qty = (int)$this->request->getPost('quantity');
        $expiry = $this->request->getPost('expiry_date') ?: null;

        if ($qty <= 0) {
            return redirect()->back()->with('error', 'Quantity must be positive');
        }

        $db = db_connect();
        $db->transStart();
        $db->table('branch_stocks')->insert([
            'branch_id' => $branchId,
            'item_id' => $itemId,
            'quantity' => $qty,
            'expiry_date' => $expiry,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $db->table('activity_logs')->insert([
            'user_id' => session('user_id'),
            'action' => 'inventory_receive',
            'details' => json_encode([ 'branch_id' => $branchId, 'item_id' => $itemId, 'quantity' => $qty, 'expiry_date' => $expiry ]),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $db->transComplete();

        return redirect()->back()->with('success', 'Stock received');
    }

    public function adjust(): RedirectResponse
    {
        $branchId = (int)$this->request->getPost('branch_id');
        $itemId = (int)$this->request->getPost('item_id');
        $delta = (int)$this->request->getPost('delta');

        if ($delta === 0) {
            return redirect()->back()->with('error', 'Adjustment cannot be zero');
        }

        $db = db_connect();
        $db->transStart();
        // store as a new stock movement row (no expiry)
        $db->table('branch_stocks')->insert([
            'branch_id' => $branchId,
            'item_id' => $itemId,
            'quantity' => $delta,
            'expiry_date' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $db->table('activity_logs')->insert([
            'user_id' => session('user_id'),
            'action' => 'inventory_adjust',
            'details' => json_encode([ 'branch_id' => $branchId, 'item_id' => $itemId, 'delta' => $delta ]),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $db->transComplete();

        return redirect()->back()->with('success', 'Stock adjusted');
    }
}


