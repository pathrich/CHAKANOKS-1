<?php

namespace App\Controllers;

class SystemAdmin extends BaseController
{
    public function index()
    {
        $data['title'] = 'System Administrator';
        return view('system_admin/dashboard', $data);
    }

    public function users()
    {
        $db = db_connect();

        $users = $db->table('users')
            ->select('users.id, users.username, users.full_name')
            ->select("GROUP_CONCAT(roles.name SEPARATOR ', ') as roles", false)
            ->join('user_roles', 'user_roles.user_id = users.id', 'left')
            ->join('roles', 'roles.id = user_roles.role_id', 'left')
            ->groupBy('users.id')
            ->get()
            ->getResultArray();

        $data['title'] = 'User Management';
        $data['users'] = $users;

        return view('system_admin/users', $data);
    }

    public function backups()
    {
        $data['title'] = 'Backups & Restore';
        return view('system_admin/backups', $data);
    }

    public function security()
    {
        $data['title'] = 'Security Settings';
        return view('system_admin/security', $data);
    }
}
