<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RedirectResponse;
use App\Models\DeliveryModel;

class Transfers extends Controller
{
    public function index()
    {
        $db = db_connect();

        $transfers = $db->table('stock_transfers st')
            ->select('st.*, i.name as item_name, i.sku, fb.name as from_branch, tb.name as to_branch')
            ->join('items i', 'i.id = st.item_id', 'left')
            ->join('branches fb', 'fb.id = st.from_branch_id', 'left')
            ->join('branches tb', 'tb.id = st.to_branch_id', 'left')
            ->orderBy('st.created_at', 'DESC')
            ->get()
            ->getResult();

        return view('transfers/index', [
            'transfers' => $transfers,
            'title'     => 'Inter-Branch Transfers',
        ]);
    }

    public function my()
    {
        $db = db_connect();
        $branchId = (int)(session('branch_id') ?? 0);

        $builder = $db->table('stock_transfers st')
            ->select('st.*, i.name as item_name, i.sku, fb.name as from_branch, tb.name as to_branch')
            ->join('items i', 'i.id = st.item_id', 'left')
            ->join('branches fb', 'fb.id = st.from_branch_id', 'left')
            ->join('branches tb', 'tb.id = st.to_branch_id', 'left')
            ->orderBy('st.created_at', 'DESC');

        if ($branchId) {
            $builder->groupStart()
                ->where('st.from_branch_id', $branchId)
                ->orWhere('st.to_branch_id', $branchId)
                ->groupEnd();
        }

        $transfers = $builder->get()->getResult();

        return view('transfers/my', [
            'transfers' => $transfers,
            'title'     => 'My Transfers',
        ]);
    }

    public function approve(): RedirectResponse
    {
        $id = (int)$this->request->getPost('id');

        if (! $id) {
            return redirect()->back()->with('error', 'Invalid transfer');
        }

        $db = db_connect();
        $db->transStart();

        $transfer = $db->table('stock_transfers')->where('id', $id)->get()->getRowArray();
        if (! $transfer || $transfer['status'] !== 'Requested') {
            $db->transComplete();
            return redirect()->back()->with('error', 'Transfer not in a state that can be approved');
        }

        $db->table('stock_transfers')->where('id', $id)->update([
            'status'      => 'Approved',
            'approved_by' => session('user_id'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        // Automatically create a delivery entry for logistics to schedule this transfer
        $deliveryModel = new DeliveryModel();
        $now = date('Y-m-d H:i:s');
        $routeDescription = sprintf(
            'Branch transfer #%d: From %d to %d - Item %d, Qty %d',
            $id,
            $transfer['from_branch_id'],
            $transfer['to_branch_id'],
            $transfer['item_id'],
            $transfer['quantity']
        );

        $deliveryModel->insert([
            'order_id'         => null,
            'transfer_id'      => $id,
            'type'             => 'Transfer',
            'driver_name'      => null,
            'vehicle'          => null,
            'route'            => $routeDescription,
            'status'           => 'scheduled',
            'scheduled_at'     => $now,
            'current_location' => null,
            'created_by'       => session('user_id'),
            'created_at'       => $now,
            'updated_at'       => $now,
        ]);

        $db->table('activity_logs')->insert([
            'user_id'    => session('user_id'),
            'action'     => 'transfer_request_approved',
            'details'    => json_encode(['transfer_id' => $id]),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        return redirect()->back()->with('success', 'Transfer approved');
    }

    public function reject(): RedirectResponse
    {
        $id     = (int)$this->request->getPost('id');
        $reason = trim((string)$this->request->getPost('reason'));

        if (! $id) {
            return redirect()->back()->with('error', 'Invalid transfer');
        }

        $db = db_connect();
        $db->transStart();

        $transfer = $db->table('stock_transfers')->where('id', $id)->get()->getRowArray();
        if (! $transfer || $transfer['status'] !== 'Requested') {
            $db->transComplete();
            return redirect()->back()->with('error', 'Transfer not in a state that can be rejected');
        }

        $db->table('stock_transfers')->where('id', $id)->update([
            'status'      => 'Rejected',
            'approved_by' => session('user_id'),
            'updated_at'  => date('Y-m-d H:i:s'),
            'reason'      => $reason ?: $transfer['reason'],
        ]);

        $db->table('activity_logs')->insert([
            'user_id'    => session('user_id'),
            'action'     => 'transfer_request_rejected',
            'details'    => json_encode(['transfer_id' => $id, 'reason' => $reason]),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        return redirect()->back()->with('success', 'Transfer rejected');
    }
}
