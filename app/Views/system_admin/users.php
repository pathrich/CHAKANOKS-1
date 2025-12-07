<?php
$title = $title ?? 'User Management';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?></title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;background:#f5f5f5;color:#333}
        .navbar{background:#16a085;color:#fff;padding:1rem 2rem;display:flex;justify-content:space-between;align-items:center}
        .container{max-width:1000px;margin:2rem auto;padding:0 1rem}
        .card{background:#fff;padding:1rem;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.05)}
        table{width:100%;border-collapse:collapse;margin-top:1rem}
        th{background:#16a085;color:#fff;padding:.75rem;text-align:left}
        td{padding:.6rem;border-bottom:1px solid #eee}
        .btn{padding:.45rem .8rem;border-radius:6px;color:#fff;background:#16a085;text-decoration:none}
    </style>
</head>
<body>
    <nav class="navbar">
        <div><strong><?= esc($title) ?></strong></div>
        <div>
            <a href="<?= site_url('system-admin') ?>" style="color:white;text-decoration:none;margin-right:1rem">Back</a>
            <a href="<?= site_url('logout') ?>" style="color:white;text-decoration:none">Logout</a>
        </div>
    </nav>
    <div class="container">
        <div class="card">
            <h3>User Accounts (placeholder)</h3>
            <p>This page will list users and provide actions to reset passwords, change roles, and deactivate accounts.</p>
            <table>
                <thead>
                    <tr><th>Username</th><th>Full Name</th><th>Role(s)</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>admin</td>
                        <td>Central Admin</td>
                        <td>central_admin</td>
                        <td><a class="btn" href="#">Edit</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
