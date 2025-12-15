<?php
$title = $title ?? 'Branch Management';
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
        .card{background:#fff;padding:1rem;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.05)}
        table{width:100%;border-collapse:collapse;margin-top:1rem}
        th{background:#16a085;color:#fff;padding:.75rem;text-align:left}
        td{padding:.6rem;border-bottom:1px solid #eee}
        .btn{display:inline-block;padding:.45rem .8rem;border-radius:6px;text-decoration:none;color:#fff;background:#16a085}
        .btn-secondary{background:#95a5a6}
        .badge{display:inline-block;padding:.2rem .5rem;border-radius:999px;font-size:.75rem}
        .badge-active{background:#27ae60;color:#fff}
        .badge-inactive{background:#7f8c8d;color:#fff}
        .alert{padding:.75rem 1rem;border-radius:6px;margin-bottom:1rem}
        .alert-error{background:#e74c3c;color:#fff}
        .alert-success{background:#27ae60;color:#fff}
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
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
                <h3>Branches</h3>
                <a class="btn" href="<?= site_url('system-admin/branches/create') ?>">Add Branch</a>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>

            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Address</th>
                        <th>Contact</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($branches)): ?>
                        <?php foreach ($branches as $b): ?>
                            <tr>
                                <td><?= esc($b['name']) ?></td>
                                <td><?= esc($b['code']) ?></td>
                                <td><?= esc($b['address'] ?? $b['city'] ?? '-') ?></td>
                                <td><?= esc($b['contact_number'] ?? '-') ?></td>
                                <td>
                                    <?php $status = strtolower($b['status'] ?? 'active'); ?>
                                    <?php if ($status === 'inactive'): ?>
                                        <span class="badge badge-inactive">Inactive</span>
                                    <?php else: ?>
                                        <span class="badge badge-active">Active</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No branches found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
