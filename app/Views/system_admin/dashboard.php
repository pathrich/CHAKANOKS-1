<?php
$title = $title ?? 'System Administrator';
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
        .container{max-width:1100px;margin:2rem auto;padding:0 1rem}
        h2{color:#16a085;margin-bottom:1rem}
        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem}
        .card{background:#fff;padding:1.25rem;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.05);}
        .btn{display:inline-block;padding:.6rem 1rem;border-radius:6px;text-decoration:none;color:#fff;background:#16a085}
    </style>
</head>
<body>
    <nav class="navbar">
        <div><strong><?= esc($title) ?></strong></div>
        <div>
            <a href="<?= site_url('dashboard') ?>" style="color:white;text-decoration:none;margin-right:1rem">Main</a>
            <a href="<?= site_url('logout') ?>" style="color:white;text-decoration:none">Logout</a>
        </div>
    </nav>
    <div class="container">
        <h2>System Administrator</h2>
        <p>Maintain the SCMS system, manage user accounts, ensure data security, and perform backups.</p>

        <div class="grid" style="margin-top:1rem">
            <div class="card">
                <h3>User Management</h3>
                <p>Create, edit, lock/unlock user accounts and manage role assignments.</p>
                <p><a class="btn" href="<?= site_url('system-admin/users') ?>">Manage Users</a></p>
            </div>

            <div class="card">
                <h3>Backups & Restore</h3>
                <p>Perform system backups and restore from snapshots. Schedule automatic backups.</p>
                <p><a class="btn" href="<?= site_url('system-admin/backups') ?>">Backups</a></p>
            </div>

            <div class="card">
                <h3>Security Settings</h3>
                <p>Manage password policies, session settings, and audit logs.</p>
                <p><a class="btn" href="<?= site_url('system-admin/security') ?>">Security</a></p>
            </div>
        </div>
    </div>
</body>
</html>
