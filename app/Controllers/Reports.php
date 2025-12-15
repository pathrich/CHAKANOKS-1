<?php

namespace App\Controllers;

class Reports extends BaseController
{
    public function index()
    {
        if (! session('user_id')) {
            return redirect()->to(site_url('login'));
        }

        $db = db_connect();

        // Basic high-level metrics to show on the reports landing page
        $data = [];

        // Branches
        $data['totalBranches'] = $db->table('branches')->countAllResults();

        // Users
        $data['totalUsers'] = $db->table('users')->countAllResults();

        // Items
        if ($db->tableExists('items')) {
            $data['totalItems'] = $db->table('items')->countAllResults();
        } else {
            $data['totalItems'] = null;
        }

        // Supply requests (if table exists)
        if ($db->tableExists('supply_requests')) {
            $data['pendingSupplyRequests'] = $db->table('supply_requests')->where('status', 'Pending')->countAllResults();
        } else {
            $data['pendingSupplyRequests'] = null;
        }

        $data['title'] = 'Reports';

        return view('reports/index', $data);
    }
}
