<?php
$title = $title ?? 'Security Settings';
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
        .container{max-width:900px;margin:2rem auto;padding:0 1rem}
        .card{background:#fff;padding:1rem;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.05)}
        .btn{padding:.6rem 1rem;border-radius:6px;color:#fff;background:#16a085;text-decoration:none}
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
            <h3>Security Settings (placeholder)</h3>
            <p>Manage password policy, session timeout, 2FA settings, and audit log configuration.</p>
            <p><a class="btn" href="#">Open Settings</a></p>
        </div>
    </div>
</body>
</html>
