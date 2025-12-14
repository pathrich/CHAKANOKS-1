<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DeliveryModel;
use CodeIgniter\I18n\Time;

class Delivery extends BaseController
{
    protected $deliveryModel;

    public function __construct()
    {
        $this->deliveryModel = new DeliveryModel();
    }

    // List deliveries
    public function index()
    {
        $data['title'] = 'Deliveries';
        $data['deliveries'] = $this->deliveryModel->orderBy('scheduled_at','DESC')->findAll();
        return view('logistics/index', $data);
    }

    // Show create form
    public function create()
    {
        $data['title'] = 'Schedule Delivery';
        return view('logistics/create', $data);
    }

    // Store delivery
    public function store()
    {
        $now = Time::now('UTC')->toDateTimeString();
        $payload = [
            'order_id'     => $this->request->getPost('order_id'),
            'transfer_id'  => null,
            'type'         => 'PO',
            'driver_name'  => $this->request->getPost('driver_name'),
            'vehicle'      => $this->request->getPost('vehicle'),
            'route'        => $this->request->getPost('route'),
            'status'       => 'scheduled',
            'scheduled_at' => $this->request->getPost('scheduled_at'),
            'created_by'   => session('user_id'),
            'created_at'   => $now,
            'updated_at'   => $now,
        ];

        $this->deliveryModel->insert($payload);
        return redirect()->to(site_url('deliveries'))->with('success','Delivery scheduled');
    }

    // Basic tracking view
    public function track($id = null)
    {
        $d = $this->deliveryModel->find($id);
        if (!$d) return redirect()->to(site_url('deliveries'))->with('error','Delivery not found');
        $data['title'] = 'Track Delivery';
        $data['delivery'] = $d;
        return view('logistics/track', $data);
    }

    // Mark delivery as delivered and, if linked to a transfer, update inventory
    public function markDelivered()
    {
        $deliveryId = (int)$this->request->getPost('id');

        if (! $deliveryId) {
            return redirect()->back()->with('error', 'Invalid delivery');
        }

        $db = db_connect();
        $delivery = $db->table('deliveries')->where('id', $deliveryId)->get()->getRowArray();
        if (! $delivery) {
            return redirect()->back()->with('error', 'Delivery not found');
        }

        $db->transStart();

        // Update delivery status
        $db->table('deliveries')->where('id', $deliveryId)->update([
            'status'     => 'delivered',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // If this delivery is linked to a stock transfer, update inventory and transfer status
        if (! empty($delivery['transfer_id'])) {
            $transfer = $db->table('stock_transfers')->where('id', (int)$delivery['transfer_id'])->get()->getRowArray();

            if ($transfer && $transfer['status'] === 'Approved') {
                $fromBranch = (int)$transfer['from_branch_id'];
                $toBranch   = (int)$transfer['to_branch_id'];
                $itemId     = (int)$transfer['item_id'];
                $qty        = (int)$transfer['quantity'];

                // OUT from source branch
                $db->table('branch_stocks')->insert([
                    'branch_id'   => $fromBranch,
                    'item_id'     => $itemId,
                    'quantity'    => -$qty,
                    'expiry_date' => null,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);

                // IN to destination branch
                $db->table('branch_stocks')->insert([
                    'branch_id'   => $toBranch,
                    'item_id'     => $itemId,
                    'quantity'    => $qty,
                    'expiry_date' => null,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);

                // Mark transfer as completed
                $db->table('stock_transfers')->where('id', $transfer['id'])->update([
                    'status'     => 'Completed',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                // Log activity
                $db->table('activity_logs')->insert([
                    'user_id'    => session('user_id'),
                    'action'     => 'transfer_completed',
                    'details'    => json_encode([
                        'transfer_id'    => $transfer['id'],
                        'from_branch_id' => $fromBranch,
                        'to_branch_id'   => $toBranch,
                        'item_id'        => $itemId,
                        'quantity'       => $qty,
                    ]),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        $db->transComplete();

        return redirect()->back()->with('success', 'Delivery marked as delivered and inventory updated');
    }

    // Simple route optimization stub - accepts JSON of stops and returns an ordered list
    public function optimizeRoute()
    {
        $payload = $this->request->getJSON(true);
        if (empty($payload['stops']) || !is_array($payload['stops'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid payload']);
        }

        // Very simple heuristic: sort by latitude then longitude - placeholder for real optimization
        $stops = $payload['stops'];
        usort($stops, function($a, $b){
            $latA = $a['lat'] ?? 0; $latB = $b['lat'] ?? 0;
            if ($latA == $latB) return ($a['lng'] ?? 0) <=> ($b['lng'] ?? 0);
            return $latA <=> $latB;
        });

        return $this->response->setJSON(['route' => $stops]);
    }
}
