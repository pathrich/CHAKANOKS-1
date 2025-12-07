<?php
$title = $title ?? 'Track Delivery';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f5f5; color: #333; }
        .navbar { background-color: #16a085; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .navbar h1 { font-size: 1.5rem; }
        .container { max-width: 1000px; margin: 2rem auto; padding: 0 1rem; }
        .section { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        h2 { color: #16a085; margin-bottom: 1rem; font-size: 1.6rem; }
        .detail { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .card { background:#fff; padding:1rem; border-radius:8px; box-shadow:0 1px 4px rgba(0,0,0,0.05); }
        .label { color:#666; font-weight:600; margin-bottom:0.25rem; }
        pre { background:#f8f9fa; padding:1rem; border-radius:6px; overflow:auto; }
        .btn { padding: 0.6rem 1rem; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; color: white; }
        .btn-secondary { background-color: #95a5a6; }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1><?= esc($title) ?></h1>
        <div class="nav-links">
            <a href="<?= site_url('order') ?>" style="color:white; text-decoration:none; margin-left:1rem;">Orders</a>
            <a href="<?= site_url('deliveries') ?>" style="color:white; text-decoration:none; margin-left:1rem;">Deliveries</a>
            <a href="<?= site_url('logout') ?>" style="color:white; text-decoration:none; margin-left:1rem;">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="section">
            <h2>Delivery Details</h2>
            <div class="detail">
                <div class="card">
                    <div class="label">Delivery ID</div>
                    <div><?= esc($delivery['id']) ?></div>
                    <div class="label">Order ID</div>
                    <div><?= esc($delivery['order_id'] ?? '-') ?></div>
                    <div class="label">Driver</div>
                    <div><?= esc($delivery['driver_name']) ?></div>
                    <div class="label">Vehicle</div>
                    <div><?= esc($delivery['vehicle']) ?></div>
                    <div class="label">Status</div>
                    <div><?= esc($delivery['status']) ?></div>
                    <div class="label">Scheduled At</div>
                    <div><?= esc($delivery['scheduled_at']) ?></div>
                    <div class="label">Current Location</div>
                    <div><?= esc($delivery['current_location']) ?></div>
                </div>

                <div class="card">
                    <div class="label">Route</div>
                    <pre><?= esc($delivery['route']) ?></pre>
                </div>
            </div>

            <div style="margin-top:1rem;">
                <a href="<?= site_url('deliveries') ?>" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
</body>
</html>
