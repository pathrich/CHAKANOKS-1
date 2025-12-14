<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session('user_id')) {
            return redirect()->to(site_url('login'));
        }
        if (empty($arguments)) {
            return; // no specific role required
        }

        try {
            $db = db_connect();
            $roles = $db->table('roles')->select('roles.name')
                ->join('user_roles', 'user_roles.role_id = roles.id')
                ->where('user_roles.user_id', session('user_id'))
                ->get()->getResultArray();

            $userRoles = array_map(fn($r) => $r['name'], $roles);
            
            // Log for debugging
            log_message('debug', 'RoleFilter - User ID: ' . session('user_id') . ', User Roles: ' . json_encode($userRoles) . ', Required: ' . json_encode($arguments));
            
            foreach ($arguments as $need) {
                if (in_array($need, $userRoles, true)) {
                    return; // authorized
                }
            }

            // User is logged in but lacks the required role.
            // Keep the session and send them to their dashboard with an error message
            // instead of bouncing them back to the login screen.
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'You are not authorized to access this resource');
        } catch (\Exception $e) {
            log_message('error', 'RoleFilter Exception: ' . $e->getMessage());
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'An error occurred during authorization');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}


