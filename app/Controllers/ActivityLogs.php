<?php

namespace App\Controllers;

class ActivityLogs extends BaseController
{
    public function index()
    {
        if (! session('user_id')) {
            return redirect()->to(site_url('login'));
        }

        $db = db_connect();

        // Fetch activity logs for the current user (most recent first)
        $logs = $db->table('activity_logs')
            ->select('activity_logs.*, users.full_name')
            ->join('users', 'users.id = activity_logs.user_id', 'left')
            ->where('activity_logs.user_id', session('user_id'))
            ->orderBy('activity_logs.created_at', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'My Activity Logs',
            'logs'  => $logs,
        ];

        return view('activity_logs/index', $data);
    }
}
