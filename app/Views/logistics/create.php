<?php
$title = $title ?? 'Schedule Delivery';
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
        .navbar a { color: white; text-decoration: none; margin-left: 2rem; padding: 0.5rem 1rem; border-radius: 4px; background-color: #e74c3c; }
        .container { max-width: 900px; margin: 2rem auto; padding: 0 1rem; }
        h2 { color: #16a085; margin-bottom: 1rem; font-size: 1.6rem; }
        .section { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .form-row { margin-bottom: 1rem; }
        label { display:block; margin-bottom:0.4rem; color:#555; }
        input[type="text"], input[type="datetime-local"], textarea { width:100%; padding:0.6rem; border:1px solid #e0e0e0; border-radius:6px; }
        textarea { min-height:120px; }
        .btn { padding: 0.6rem 1rem; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; color: white; }
        .btn-primary { background-color: #16a085; }
        .btn-secondary { background-color: #95a5a6; }
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
            <h2>Schedule Delivery</h2>
            <form method="post" action="<?= site_url('deliveries/store') ?>">
                <div class="form-row">
                    <label for="order_id">Order ID</label>
                    <input id="order_id" type="text" name="order_id" value="<?= set_value('order_id') ?>" />
                </div>
                <div class="form-row">
                    <label for="driver_name">Driver Name</label>
                    <input id="driver_name" type="text" name="driver_name" value="<?= set_value('driver_name') ?>" />
                </div>
                <div class="form-row">
                    <label for="vehicle">Vehicle</label>
                    <input id="vehicle" type="text" name="vehicle" value="<?= set_value('vehicle') ?>" />
                </div>
                <div class="form-row">
                    <label for="scheduled_at">Scheduled At</label>
                    <input id="scheduled_at" type="datetime-local" name="scheduled_at" value="<?= set_value('scheduled_at') ?>" />
                </div>
                <div class="form-row">
                    <label for="route">Route (JSON)</label>
                    <textarea id="route" name="route" placeholder='[{"lat":...,"lng":...}, ...]'><?= set_value('route') ?></textarea>
                </div>

                <div style="display:flex; gap:0.5rem;">
                    <button class="btn btn-primary" type="submit">Schedule</button>
                    <a href="<?= site_url('deliveries') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
