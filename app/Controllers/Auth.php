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


