<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
        // Get user roles
        $db = db_connect();
        $roles = $db->table('roles')->select('roles.name')
            ->join('user_roles', 'user_roles.role_id = roles.id')
            ->where('user_roles.user_id', session('user_id'))
            ->get()->getResultArray();

        $userRoles = array_map(fn($r) => $r['name'], $roles);
        
        log_message('debug', 'Dashboard Access - User ID: ' . session('user_id') . ', Roles: ' . json_encode($userRoles));

        // Check if user has admin or manager role
        if (in_array('central_admin', $userRoles, true)) {
            return $this->adminDashboard();
        } elseif (in_array('branch_manager', $userRoles, true)) {
            return $this->managerDashboard();
        }

        // If user has no admin/manager role, show an access denied message
        $data = [
            'userRoles' => $userRoles,
        ];
        return view('dashboard/unauthorized', $data);
    }

    protected function adminDashboard()
    {
        $db = db_connect();
        
        // Get dashboard statistics
        $totalBranches = $db->table('branches')->countAllResults();
        $totalUsers = $db->table('users')->countAllResults();
        $totalItems = $db->table('items')->countAllResults();
        $totalCategories = $db->table('item_categories')->countAllResults();

        // Get recent activity logs
        $activityLogs = $db->table('activity_logs')
            ->select('activity_logs.*, users.full_name')
            ->join('users', 'users.id = activity_logs.user_id', 'left')
            ->orderBy('activity_logs.created_at', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        // Get low stock items - simplified approach
        $allItems = $db->table('branch_stocks')
            ->select('items.name, items.min_stock, branch_stocks.quantity, branches.name as branch_name')
            ->join('items', 'items.id = branch_stocks.item_id')
            ->join('branches', 'branches.id = branch_stocks.branch_id')
            ->limit(50)
            ->get()->getResultArray();
        
        // Filter low stock items in PHP
        $lowStockItems = array_filter($allItems, function($item) {
            $minStock = $item['min_stock'] ?? 10;
            return $item['quantity'] < $minStock;
        });
        
        // Limit to 10
        $lowStockItems = array_slice($lowStockItems, 0, 10);

        $data = [
            'totalBranches' => $totalBranches,
            'totalUsers' => $totalUsers,
            'totalItems' => $totalItems,
            'totalCategories' => $totalCategories,
            'activityLogs' => $activityLogs,
            'lowStockItems' => $lowStockItems,
        ];

        return view('dashboard/admin', $data);
    }

    protected function managerDashboard()
    {
        $db = db_connect();
        
        // Get user's branch
        $user = $db->table('users')
            ->select('branch_id')
            ->where('id', session('user_id'))
            ->get()->getRow();

        $branchId = $user->branch_id ?? null;

        // Get branch statistics
        $branchData = [];
        if ($branchId) {
            $branch = $db->table('branches')->where('id', $branchId)->get()->getRow();
            $branchData['branch'] = $branch;

            // Total stock count for this branch
            $branchData['totalStock'] = $db->table('branch_stocks')
                ->selectSum('quantity')
                ->where('branch_id', $branchId)
                ->get()->getRow()->quantity ?? 0;

            // Get items in this branch
            $branchData['itemCount'] = $db->table('branch_stocks')
                ->where('branch_id', $branchId)
                ->countAllResults();

            // Get low stock items in this branch - simplified
            $items = $db->table('branch_stocks')
                ->select('items.name, items.min_stock, branch_stocks.quantity')
                ->join('items', 'items.id = branch_stocks.item_id')
                ->where('branch_stocks.branch_id', $branchId)
                ->limit(50)
                ->get()->getResultArray();
            
            // Filter low stock items in PHP
            $lowStockItems = array_filter($items, function($item) {
                $minStock = $item['min_stock'] ?? 10;
                return $item['quantity'] < $minStock;
            });
            
            // Limit to 10
            $branchData['lowStockItems'] = array_slice($lowStockItems, 0, 10);

            // Get recent activity for this branch
            $branchData['recentActivity'] = $db->table('activity_logs')
                ->select('activity_logs.*, users.full_name')
                ->join('users', 'users.id = activity_logs.user_id', 'left')
                ->where('users.branch_id', $branchId)
                ->orderBy('activity_logs.created_at', 'DESC')
                ->limit(10)
                ->get()->getResultArray();
        }

        $data = [
            'branchData' => $branchData,
        ];

        return view('dashboard/manager', $data);
    }
}
