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
            'SELECT i.id, i.name, i.sku, i.barcode, i.min_stock, i.perishable,
                    COALESCE(SUM(bs.quantity), 0) AS quantity,
                    MIN(bs.expiry_date) AS nearest_expiry
             FROM items i
             LEFT JOIN branch_stocks bs ON bs.item_id = i.id AND bs.branch_id = ?
             GROUP BY i.id, i.name, i.sku, i.barcode, i.min_stock, i.perishable',
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
        $branch = $db->table('branches')->select('id')->where('id', $branchId)->get()->getRowArray();
        if (! $branch) {
            return redirect()->back()->with('error', 'Invalid branch selected for receiving stock');
        }

        try {
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
        } catch (\Throwable $e) {
            if ($db->transStatus() !== false) {
                $db->transComplete();
            }
            log_message('error', 'Inventory receive failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to receive stock: ' . $e->getMessage());
        }

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
        $branch = $db->table('branches')->select('id')->where('id', $branchId)->get()->getRowArray();
        if (! $branch) {
            return redirect()->back()->with('error', 'Invalid branch selected for stock adjustment');
        }

        try {
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
        } catch (\Throwable $e) {
            if ($db->transStatus() !== false) {
                $db->transComplete();
            }
            log_message('error', 'Inventory adjust failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to adjust stock: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Stock adjusted');
    }

    public function markExpired(): RedirectResponse
    {
        $branchId = (int)$this->request->getPost('branch_id');
        $itemId   = (int)$this->request->getPost('item_id');
        $qty      = (int)$this->request->getPost('quantity');

        if ($qty <= 0) {
            return redirect()->back()->with('error', 'Quantity must be positive');
        }

        $db = db_connect();
        $branch = $db->table('branches')->select('id')->where('id', $branchId)->get()->getRowArray();
        if (! $branch) {
            return redirect()->back()->with('error', 'Invalid branch selected for expired stock');
        }

        try {
            $db->transStart();

            // Record as a negative movement (expired)
            $db->table('branch_stocks')->insert([
                'branch_id'   => $branchId,
                'item_id'     => $itemId,
                'quantity'    => -$qty,
                'expiry_date' => null,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);

            $db->table('activity_logs')->insert([
                'user_id'    => session('user_id'),
                'action'     => 'inventory_expired',
                'details'    => json_encode(['branch_id' => $branchId, 'item_id' => $itemId, 'quantity' => $qty]),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $db->transComplete();
        } catch (\Throwable $e) {
            if ($db->transStatus() !== false) {
                $db->transComplete();
            }
            log_message('error', 'Inventory markExpired failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to record expired stock: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Expired stock recorded');
    }

    public function markDamaged(): RedirectResponse
    {
        $branchId = (int)$this->request->getPost('branch_id');
        $itemId   = (int)$this->request->getPost('item_id');
        $qty      = (int)$this->request->getPost('quantity');

        if ($qty <= 0) {
            return redirect()->back()->with('error', 'Quantity must be positive');
        }

        $db = db_connect();
        $branch = $db->table('branches')->select('id')->where('id', $branchId)->get()->getRowArray();
        if (! $branch) {
            return redirect()->back()->with('error', 'Invalid branch selected for damaged stock');
        }

        try {
            $db->transStart();

            // Record as a negative movement (damaged)
            $db->table('branch_stocks')->insert([
                'branch_id'   => $branchId,
                'item_id'     => $itemId,
                'quantity'    => -$qty,
                'expiry_date' => null,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);

            $db->table('activity_logs')->insert([
                'user_id'    => session('user_id'),
                'action'     => 'inventory_damaged',
                'details'    => json_encode(['branch_id' => $branchId, 'item_id' => $itemId, 'quantity' => $qty]),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $db->transComplete();
        } catch (\Throwable $e) {
            if ($db->transStatus() !== false) {
                $db->transComplete();
            }
            log_message('error', 'Inventory markDamaged failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to record damaged stock: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Damaged stock recorded');
    }

    public function history(int $itemId)
    {
        $branchId = (int)($this->request->getGet('branch_id') ?? 1);

        $db = db_connect();

        $item = $db->table('items')->where('id', $itemId)->get()->getRow();

        $movements = $db->table('branch_stocks')
            ->select('quantity, expiry_date, created_at')
            ->where('branch_id', $branchId)
            ->where('item_id', $itemId)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();

        return view('inventory/history', [
            'item'      => $item,
            'movements' => $movements,
            'branchId'  => $branchId,
        ]);
    }

    public function acknowledgeLowStock(): RedirectResponse
    {
        $branchId = (int)$this->request->getPost('branch_id');
        $itemId   = (int)$this->request->getPost('item_id');

        if (! $branchId || ! $itemId) {
            return redirect()->back()->with('error', 'Invalid low stock acknowledgement');
        }

        $db = db_connect();
        $db->table('activity_logs')->insert([
            'user_id'    => session('user_id'),
            'action'     => 'low_stock_acknowledged',
            'details'    => json_encode(['branch_id' => $branchId, 'item_id' => $itemId]),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Low stock alert acknowledged');
    }

    public function requestTransfer(): RedirectResponse
    {
        $fromBranchId = (int)$this->request->getPost('from_branch_id');
        $toBranchId   = (int)$this->request->getPost('to_branch_id');
        $itemId       = (int)$this->request->getPost('item_id');
        $qty          = (int)$this->request->getPost('quantity');
        $reason       = trim((string)$this->request->getPost('reason'));

        if ($qty <= 0) {
            return redirect()->back()->with('error', 'Transfer quantity must be positive');
        }
        if ($fromBranchId === $toBranchId || $toBranchId <= 0) {
            return redirect()->back()->with('error', 'Please choose a different target branch');
        }

        $db = db_connect();
        $db->transStart();

        $db->table('stock_transfers')->insert([
            'from_branch_id' => $fromBranchId,
            'to_branch_id'   => $toBranchId,
            'item_id'        => $itemId,
            'quantity'       => $qty,
            'status'         => 'Requested',
            'requested_by'   => session('user_id'),
            'reason'         => $reason ?: null,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);

        $db->table('activity_logs')->insert([
            'user_id'    => session('user_id'),
            'action'     => 'transfer_request_created',
            'details'    => json_encode([
                'from_branch_id' => $fromBranchId,
                'to_branch_id'   => $toBranchId,
                'item_id'        => $itemId,
                'quantity'       => $qty,
            ]),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        return redirect()->back()->with('success', 'Transfer request submitted');
    }
}


