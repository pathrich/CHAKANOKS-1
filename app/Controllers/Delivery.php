<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DeliveryModel;
use App\Models\NotificationModel;
use CodeIgniter\I18n\Time;

class Delivery extends BaseController
{
    protected $deliveryModel;
    protected $notificationModel;

    public function __construct()
    {
        $this->deliveryModel = new DeliveryModel();
        $this->notificationModel = new NotificationModel();
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

    // Show edit form for an existing delivery
    public function edit($id = null)
    {
        $d = $this->deliveryModel->find($id);
        if (!$d) {
            return redirect()->to(site_url('deliveries'))->with('error', 'Delivery not found');
        }

        $data['title'] = 'Update Delivery Schedule';
        $data['delivery'] = $d;
        return view('logistics/edit', $data);
    }

    // Store delivery
    public function store()
    {
        $now = Time::now('UTC')->toDateTimeString();

        $db = db_connect();
        $orderIdRaw = trim((string) $this->request->getPost('order_id'));
        $orderId = $orderIdRaw === '' ? null : (int) $orderIdRaw;

        if ($orderId !== null) {
            $orderExists = $db->table('orders')->select('id')->where('id', $orderId)->get()->getRowArray();
            if (! $orderExists) {
                return redirect()->back()->withInput()->with('error', 'Order ID not found. Please leave it blank unless this delivery is for an Order.');
            }
        }

        $payload = [
            'order_id'     => $orderId,
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

        if ($db->tableExists('deliveries') && $db->fieldExists('expected_delivered_at', 'deliveries')) {
            $payload['expected_delivered_at'] = $this->request->getPost('expected_delivered_at');
        }

        $deliveryId = $this->deliveryModel->insert($payload);

        if ($deliveryId) {
            $this->notifyDeliveryScheduled((int) $deliveryId);
        }
        return redirect()->to(site_url('deliveries'))->with('success','Delivery scheduled');
    }

    // Update an existing delivery schedule
    public function update($id = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return redirect()->to(site_url('deliveries'))->with('error', 'Invalid delivery');
        }

        $existing = $this->deliveryModel->find($id);
        if (!$existing) {
            return redirect()->to(site_url('deliveries'))->with('error', 'Delivery not found');
        }

        $db = db_connect();
        $now = Time::now('UTC')->toDateTimeString();

        $orderIdRaw = trim((string) $this->request->getPost('order_id'));
        $orderId = $orderIdRaw === '' ? null : (int) $orderIdRaw;

        $payload = [
            'driver_name'  => $this->request->getPost('driver_name'),
            'vehicle'      => $this->request->getPost('vehicle'),
            'route'        => $this->request->getPost('route'),
            'scheduled_at' => $this->request->getPost('scheduled_at'),
            'updated_at'   => $now,
        ];

        if ($db->tableExists('deliveries') && $db->fieldExists('expected_delivered_at', 'deliveries')) {
            $payload['expected_delivered_at'] = $this->request->getPost('expected_delivered_at');
        }

        // Only update order_id if user supplied a value
        if ($orderIdRaw !== '') {
            if ($orderId !== null) {
                $orderExists = $db->table('orders')->select('id')->where('id', $orderId)->get()->getRowArray();
                if (! $orderExists) {
                    return redirect()->back()->withInput()->with('error', 'Order ID not found. Please leave it blank unless this delivery is for an Order.');
                }
            }
            $payload['order_id'] = $orderId;
        }

        $this->deliveryModel->update($id, $payload);
        $this->notifyDeliveryScheduled($id);
        return redirect()->to(site_url('deliveries'))->with('success', 'Delivery schedule updated');
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

        // Notify stakeholders this delivery is completed
        $this->notifyDeliveryCompleted($delivery);

        return redirect()->back()->with('success', 'Delivery marked as delivered and inventory updated');
    }

    private function notifyDeliveryScheduled(int $deliveryId): void
    {
        $db = db_connect();
        $delivery = $db->table('deliveries')->where('id', $deliveryId)->get()->getRowArray();
        if (!$delivery) {
            return;
        }

        // Always notify central admin(s)
        $admins = $db->table('user_roles')
            ->select('users.id')
            ->join('roles', 'roles.id = user_roles.role_id')
            ->join('users', 'users.id = user_roles.user_id')
            ->where('roles.name', 'central_admin')
            ->get()->getResultArray();

        $ref = ($delivery['type'] ?? 'Delivery') . ' #' . $deliveryId;
        if (($delivery['type'] ?? '') === 'PO' && !empty($delivery['current_location'])) {
            $ref = $delivery['current_location'];
        }

        $expected = null;
        if ($db->fieldExists('expected_delivered_at', 'deliveries')) {
            $expected = $delivery['expected_delivered_at'] ?? null;
        }
        $expectedText = $expected ? (' Expected: ' . $expected . '.') : '';
        $msg = 'Delivery scheduled/updated. Driver: ' . ($delivery['driver_name'] ?? '-') . '. Scheduled: ' . ($delivery['scheduled_at'] ?? '-') . '.' . $expectedText;

        foreach ($admins as $a) {
            $uid = (int) ($a['id'] ?? 0);
            if ($uid > 0) {
                $this->notificationModel->createNotification($uid, 'delivery_scheduled', $ref . ' scheduled', $msg, $deliveryId, 'delivery');
            }
        }

        // If this is a PO delivery, try to notify the branch manager for the related PO
        if (($delivery['type'] ?? '') === 'PO' && !empty($delivery['current_location'])) {
            $token = (string) $delivery['current_location'];
            if (strpos($token, 'PO#') === 0) {
                $poId = (int) substr($token, 3);
                if ($poId > 0 && $db->tableExists('purchase_orders')) {
                    $po = $db->table('purchase_orders')->where('id', $poId)->get()->getRowArray();
                    if ($po && !empty($po['supply_request_id']) && $db->tableExists('supply_requests')) {
                        $sr = $db->table('supply_requests')->where('id', (int) $po['supply_request_id'])->get()->getRowArray();
                        $branchId = (int) ($sr['branch_id'] ?? 0);
                        if ($branchId > 0) {
                            $manager = $db->table('users')
                                ->select('users.id')
                                ->join('user_roles', 'user_roles.user_id = users.id')
                                ->join('roles', 'roles.id = user_roles.role_id')
                                ->where('roles.name', 'branch_manager')
                                ->where('users.branch_id', $branchId)
                                ->get()->getRowArray();
                            if ($manager) {
                                $this->notificationModel->createNotification(
                                    (int) $manager['id'],
                                    'delivery_scheduled',
                                    $ref . ' scheduled',
                                    $msg,
                                    $deliveryId,
                                    'delivery'
                                );
                            }
                        }
                    }

                    // Notify supplier users linked to this PO's supplier_id
                    $supplierId = (int) ($po['supplier_id'] ?? 0);
                    if ($supplierId > 0 && $db->tableExists('user_suppliers')) {
                        $supplierUsers = $db->table('user_suppliers')
                            ->select('users.id as user_id')
                            ->join('users', 'users.id = user_suppliers.user_id')
                            ->where('user_suppliers.supplier_id', $supplierId)
                            ->get()->getResultArray();
                        foreach ($supplierUsers as $su) {
                            $uid = (int) ($su['user_id'] ?? 0);
                            if ($uid > 0) {
                                $this->notificationModel->createNotification(
                                    $uid,
                                    'delivery_scheduled',
                                    $ref . ' scheduled',
                                    $msg,
                                    $deliveryId,
                                    'delivery'
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    private function notifyDeliveryCompleted(array $delivery): void
    {
        $db = db_connect();

        $ref = ($delivery['type'] ?? 'Delivery') . ' #' . ($delivery['id'] ?? '');
        if (($delivery['type'] ?? '') === 'PO' && !empty($delivery['current_location'])) {
            $ref = $delivery['current_location'];
        }

        // Central admins
        $admins = $db->table('user_roles')
            ->select('users.id')
            ->join('roles', 'roles.id = user_roles.role_id')
            ->join('users', 'users.id = user_roles.user_id')
            ->where('roles.name', 'central_admin')
            ->get()->getResultArray();

        foreach ($admins as $a) {
            $uid = (int) ($a['id'] ?? 0);
            if ($uid > 0) {
                $this->notificationModel->createNotification($uid, 'delivery_delivered', $ref . ' delivered', 'Delivery marked as delivered.', (int) $delivery['id'], 'delivery');
            }
        }

        // If PO-linked, notify supplier users too
        if (($delivery['type'] ?? '') === 'PO' && !empty($delivery['current_location'])) {
            $token = (string) $delivery['current_location'];
            if (strpos($token, 'PO#') === 0) {
                $poId = (int) substr($token, 3);
                if ($poId > 0 && $db->tableExists('purchase_orders') && $db->tableExists('user_suppliers')) {
                    $po = $db->table('purchase_orders')->where('id', $poId)->get()->getRowArray();
                    $supplierId = (int) ($po['supplier_id'] ?? 0);
                    if ($supplierId > 0) {
                        $supplierUsers = $db->table('user_suppliers')
                            ->select('users.id as user_id')
                            ->join('users', 'users.id = user_suppliers.user_id')
                            ->where('user_suppliers.supplier_id', $supplierId)
                            ->get()->getResultArray();
                        foreach ($supplierUsers as $su) {
                            $uid = (int) ($su['user_id'] ?? 0);
                            if ($uid > 0) {
                                $this->notificationModel->createNotification($uid, 'delivery_delivered', $ref . ' delivered', 'Delivery marked as delivered.', (int) $delivery['id'], 'delivery');
                            }
                        }
                    }
                }
            }
        }
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
