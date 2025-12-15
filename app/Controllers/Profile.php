<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class Profile extends BaseController
{
    public function index()
    {
        if (! session('user_id')) {
            return redirect()->to(site_url('login'));
        }

        $db = db_connect();

        $user = $db->table('users')
            ->select('id, username, full_name, created_at, updated_at')
            ->where('id', session('user_id'))
            ->get()
            ->getRowArray();

        if (! $user) {
            return redirect()->to(site_url('logout'));
        }

        $data = [
            'title' => 'My Profile',
            'user'  => $user,
        ];

        return view('profile/index', $data);
    }

    public function update(): RedirectResponse
    {
        if (! session('user_id')) {
            return redirect()->to(site_url('login'));
        }

        $db = db_connect();

        $username = trim((string) $this->request->getPost('username'));
        $fullName = trim((string) $this->request->getPost('full_name'));

        if ($username === '') {
            return redirect()->back()->with('error', 'Username is required')->withInput();
        }

        // Ensure username is unique (except current user)
        $existing = $db->table('users')
            ->select('id')
            ->where('username', $username)
            ->where('id !=', session('user_id'))
            ->get()
            ->getRowArray();

        if ($existing) {
            return redirect()->back()->with('error', 'Username is already taken')->withInput();
        }

        $db->table('users')
            ->where('id', session('user_id'))
            ->update([
                'username'   => $username,
                'full_name'  => $fullName !== '' ? $fullName : null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        // Refresh session display name if changed
        session()->set('user_full_name', $fullName !== '' ? $fullName : $username);

        return redirect()->back()->with('success', 'Profile updated successfully');
    }

    public function changePassword(): RedirectResponse
    {
        if (! session('user_id')) {
            return redirect()->to(site_url('login'));
        }

        $current = (string) $this->request->getPost('current_password');
        $new     = (string) $this->request->getPost('new_password');
        $confirm = (string) $this->request->getPost('confirm_password');

        if ($new === '' || $confirm === '') {
            return redirect()->back()->with('error', 'New password is required')->withInput();
        }

        if ($new !== $confirm) {
            return redirect()->back()->with('error', 'New password and confirmation do not match')->withInput();
        }

        $db = db_connect();

        $user = $db->table('users')
            ->select('id, password_hash')
            ->where('id', session('user_id'))
            ->get()
            ->getRow();

        if (! $user || ! password_verify($current, $user->password_hash)) {
            return redirect()->back()->with('error', 'Current password is incorrect')->withInput();
        }

        $db->table('users')
            ->where('id', $user->id)
            ->update([
                'password_hash' => password_hash($new, PASSWORD_DEFAULT),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);

        return redirect()->back()->with('success', 'Password changed successfully');
    }
}
