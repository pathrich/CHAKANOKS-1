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

            // Redirect based on role
            if (in_array('logistics_coordinator', $userRoles, true)) {
                return redirect()->to(site_url('deliveries'));
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
}


