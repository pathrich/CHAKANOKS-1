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
            'order_id' => $this->request->getPost('order_id'),
            'driver_name' => $this->request->getPost('driver_name'),
            'vehicle' => $this->request->getPost('vehicle'),
            'route' => $this->request->getPost('route'),
            'status' => 'scheduled',
            'scheduled_at' => $this->request->getPost('scheduled_at'),
            'created_by' => session('user_id'),
            'created_at' => $now,
            'updated_at' => $now,
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
