<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Auth extends Controller
{
    public function login()
    {
        return view('auth/login');
    }

    public function doLogin()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $db = db_connect();
        $user = $db->table('users')->where('username', $username)->get()->getRow();
        if ($user && password_verify($password, $user->password_hash)) {
            session()->set('user_id', $user->id);
            session()->set('user_full_name', $user->full_name);
            
            // Get user roles
            $roles = $db->table('roles')->select('roles.name')
                ->join('user_roles', 'user_roles.role_id = roles.id')
                ->where('user_roles.user_id', $user->id)
                ->get()->getResultArray();

            $userRoles = array_map(fn($r) => $r['name'], $roles);

            // Store all roles and an active role in session for role-based UI and switching
            session()->set('user_roles', $userRoles);
            if (! session()->has('user_role') && ! empty($userRoles)) {
                session()->set('user_role', $userRoles[0]);
            }

            // Redirect based on role
            if (in_array('logistics_coordinator', $userRoles, true)) {
                return redirect()->to(site_url('deliveries'));
            }

            if (in_array('system_admin', $userRoles, true)) {
                return redirect()->to(site_url('system-admin'));
            }

            if (
                in_array('central_admin', $userRoles, true) ||
                in_array('branch_manager', $userRoles, true) ||
                in_array('supplier', $userRoles, true) ||
                in_array('franchise', $userRoles, true)
            ) {
                return redirect()->to(site_url('dashboard'));
            }

            return redirect()->to(site_url('inventory'));
        }

        return redirect()->back()->with('error', 'Invalid credentials');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(site_url('login'));
    }

    /**
     * Switch the active role for the current user (session-based).
     */
    public function switchRole()
    {
        if (! session('user_id')) {
            return redirect()->to(site_url('login'));
        }

        $availableRoles = session('user_roles') ?? [];
        $requestedRole  = $this->request->getPost('role');

        if (! $requestedRole || ! in_array($requestedRole, $availableRoles, true)) {
            return redirect()->back()->with('error', 'Invalid role selection');
        }

        session()->set('user_role', $requestedRole);

        // Send the user to an appropriate home page for the selected role
        if ($requestedRole === 'logistics_coordinator') {
            return redirect()->to(site_url('deliveries'));
        }

        if ($requestedRole === 'system_admin') {
            return redirect()->to(site_url('system-admin'));
        }

        // Other roles use the dashboard as an entry point
        return redirect()->to(site_url('dashboard'));
    }
}


