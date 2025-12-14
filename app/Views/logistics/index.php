<?php
$title = $title ?? 'Deliveries';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <style>
        /* Reuse manager dashboard styles for consistent look */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f5f5; color: #333; }
        .navbar { background-color: #16a085; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .navbar h1 { font-size: 1.5rem; }
        .navbar a { color: white; text-decoration: none; margin-left: 2rem; padding: 0.5rem 1rem; border-radius: 4px; background-color: #e74c3c; }
        .navbar a:hover { background-color: #c0392b; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        h2 { color: #16a085; margin-bottom: 1rem; font-size: 1.6rem; }
        .section { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        table th { background-color: #16a085; color: white; padding: 0.75rem; text-align: left; font-weight: 600; }
        table td { padding: 0.75rem; border-bottom: 1px solid #ecf0f1; }
        table tr:hover { background-color: #f8f9fa; }
        .btn { padding: 0.6rem 1rem; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; color: white; }
        .btn-primary { background-color: #16a085; }
        .btn-secondary { background-color: #95a5a6; }
        .btn-info { background-color: #3498db; }
        .btn-sm { padding: 0.35rem 0.6rem; font-size: 0.9rem; }
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .alert-success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .alert-danger { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1><?= esc($title) ?></h1>
        <div class="nav-links">
            <a href="<?= site_url('order') ?>">Orders</a>
            <a href="<?= site_url('deliveries') ?>">Deliveries</a>
            <a href="<?= site_url('logout') ?>">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="section">
            <?php if (session('success')): ?>
                <div class="alert alert-success"><?= session('success') ?></div>
            <?php endif; ?>
            <?php if (session('error')): ?>
                <div class="alert alert-danger"><?= session('error') ?></div>
            <?php endif; ?>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h2>Scheduled Deliveries</h2>
                <div>
                    <a href="<?= site_url('deliveries/create') ?>" class="btn btn-primary">Schedule Delivery</a>
                </div>
            </div>

            <?php if (!empty($deliveries)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Order / Type</th>
                            <th>Driver</th>
                            <th>Vehicle</th>
                            <th>Scheduled</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($deliveries as $d): ?>
                        <tr>
                            <td><?= $d['id'] ?></td>
                            <td>
                                <?= $d['order_id'] ?? '-' ?><br>
                                <small class="muted"><?= esc($d['type'] ?? 'PO') ?></small>
                            </td>
                            <td><?= esc($d['driver_name']) ?></td>
                            <td><?= esc($d['vehicle']) ?></td>
                            <td><?= $d['scheduled_at'] ?></td>
                            <td><?= esc($d['status']) ?></td>
                            <td>
                                <a href="<?= site_url('deliveries/track/'.$d['id']) ?>" class="btn btn-sm btn-info">Track</a>
                                <?php if ($d['status'] !== 'delivered'): ?>
                                    <form method="post" action="<?= site_url('deliveries/mark-delivered') ?>" style="display:inline-block; margin-left:4px;">
                                        <input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-primary">Mark Delivered</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-message" style="padding:1rem; color:#7f8c8d;">
                    <p>No deliveries scheduled.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
