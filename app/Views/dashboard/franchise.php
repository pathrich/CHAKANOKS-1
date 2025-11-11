<?php
$title = 'Franchise Dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#f8fafc; color:#333 }
        .container { max-width:900px; margin:2rem auto; padding:1rem }
        .header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem }
        .card { background:white; padding:1rem; border-radius:6px; box-shadow:0 2px 6px rgba(0,0,0,0.06); }
        table { width:100%; border-collapse:collapse; }
        th,td { padding:0.75rem; border-bottom:1px solid #eef0f3 }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?= $title ?></h1>
            <div>
                <a href="<?= site_url('inventory') ?>">Inventory</a> |
                <a href="<?= site_url('logout') ?>">Logout</a>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:1rem; margin-bottom:1rem;">
            <div class="card">
                <?php if (!empty($branchData['branch'])): ?>
                    <h4>Branch</h4>
                    <p style="font-weight:700"><?= esc($branchData['branch']->name) ?> (<?= esc($branchData['branch']->code) ?>)</p>
                <?php else: ?>
                    <h4>Branch</h4>
                    <p style="font-weight:700">Not assigned</p>
                <?php endif; ?>
            </div>
            <div class="card">
                <h4>Total Items</h4>
                <p style="font-size:1.4rem; font-weight:700"><?= $branchData['itemCount'] ?? 0 ?></p>
            </div>
            <div class="card">
                <h4>Total Units</h4>
                <p style="font-size:1.4rem; font-weight:700"><?= $branchData['totalStock'] ?? 0 ?></p>
            </div>
        </div>

        <div class="card" style="margin-top:1rem">
            <h3>Recent Activity</h3>
            <?php if (!empty($branchData['recentActivity'])): ?>
                <table>
                    <thead>
                        <tr><th>User</th><th>Action</th><th>Details</th><th>When</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($branchData['recentActivity'] as $log): ?>
                            <tr>
                                <td><?= esc($log['full_name'] ?? 'N/A') ?></td>
                                <td><?= esc($log['action']) ?></td>
                                <td><?= esc($log['details'] ?? '-') ?></td>
                                <td><?= $log['created_at'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No recent activity for your branch.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
