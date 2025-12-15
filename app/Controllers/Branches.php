<?php

namespace App\Controllers;

use App\Models\BranchModel;

class Branches extends BaseController
{
    public function index()
    {
        $branchModel = new BranchModel();

        $data = [
            'title'    => 'Branches Management',
            'branches' => $branchModel->orderBy('name', 'ASC')->findAll(),
        ];

        return view('branches/index', $data);
    }

    public function store()
    {
        $name    = trim((string)$this->request->getPost('name'));
        $code    = trim((string)$this->request->getPost('code'));
        $address = trim((string)$this->request->getPost('address'));
        $contact = trim((string)$this->request->getPost('contact_number'));
        $status  = trim((string)$this->request->getPost('status')) ?: 'Active';

        if ($name === '' || $code === '') {
            return redirect()->back()->withInput()->with('error', 'Branch Name and Branch Code are required');
        }

        $branchModel = new BranchModel();

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
            'address'        => $address !== '' ? $address : null,
            'contact_number' => $contact !== '' ? $contact : null,
            'status'         => $status,
        ];

        try {
            if (! $branchModel->insert($data)) {
                $errors = $branchModel->errors();
                $message = !empty($errors) ? implode('; ', $errors) : 'Unable to create branch';
                return redirect()->back()->withInput()->with('error', $message);
            }

            return redirect()->back()->with('success', 'Branch created successfully');
        } catch (\Throwable $e) {
            log_message('error', 'Branch create failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to create branch: ' . $e->getMessage());
        }
    }
}
