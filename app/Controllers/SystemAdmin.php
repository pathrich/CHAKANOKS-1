<?php

namespace App\Controllers;

use App\Models\BranchModel;

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
            ->select('users.id, users.username, users.full_name, users.created_at, users.updated_at')
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

    public function branches()
    {
        $branchModel = new BranchModel();

        $data['title'] = 'Branch Management';
        $data['branches'] = $branchModel->orderBy('name', 'ASC')->findAll();

        return view('system_admin/branches', $data);
    }

    public function branchCreate()
    {
        $data['title'] = 'Create Branch';
        return view('system_admin/branch_create', $data);
    }

    public function branchStore()
    {
        $name   = trim((string)$this->request->getPost('name'));
        $code   = trim((string)$this->request->getPost('code'));
        $addr   = trim((string)$this->request->getPost('address'));
        $contact = trim((string)$this->request->getPost('contact_number'));
        $status = trim((string)$this->request->getPost('status')) ?: 'Active';

        if ($name === '' || $code === '') {
            return redirect()->back()->withInput()->with('error', 'Branch Name and Branch Code are required');
        }

        $branchModel = new BranchModel();

        // Check duplicates by name or code
        $existing = $branchModel
            ->groupStart()
                ->where('name', $name)
                ->orWhere('code', $code)
            ->groupEnd()
            ->first();

        if ($existing) {
            return redirect()->back()->withInput()->with('error', 'A branch with the same name or code already exists');
        }

        $data = [
            'name'           => $name,
            'code'           => $code,
            'address'        => $addr !== '' ? $addr : null,
            'contact_number' => $contact !== '' ? $contact : null,
            'status'         => $status,
        ];

        $branchModel->insert($data);

        return redirect()->to(site_url('system-admin/branches'))
            ->with('success', 'Branch created successfully');
    }
}
