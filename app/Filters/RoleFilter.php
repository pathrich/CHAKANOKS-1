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

        $db = db_connect();
        $roles = $db->table('roles')->select('roles.name')
            ->join('user_roles', 'user_roles.role_id = roles.id')
            ->where('user_roles.user_id', session('user_id'))
            ->get()->getResultArray();

        $userRoles = array_map(fn($r) => $r['name'], $roles);
        foreach ($arguments as $need) {
            if (in_array($need, $userRoles, true)) {
                return; // authorized
            }
        }

        return redirect()->to(site_url('login'))
            ->with('error', 'You are not authorized to access this resource');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}


