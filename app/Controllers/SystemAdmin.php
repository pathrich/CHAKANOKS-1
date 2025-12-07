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
        $data['title'] = 'User Management';
        // placeholder: list of users could be loaded by a model
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
