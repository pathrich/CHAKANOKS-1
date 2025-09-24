<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - SCMS</title>
    <style>
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background: #f6f7fb; margin: 0; }
        header { background:#fff; padding:16px 24px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; }
        .container { padding: 24px; }
        .grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; }
        .card { background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:16px; }
        table { width:100%; border-collapse: collapse; }
        th, td { padding: 10px 12px; border-bottom:1px solid #f1f5f9; text-align:left; font-size:14px; }
        th { background:#f8fafc; font-weight:600; color:#334155; }
        .badge { padding:2px 8px; border-radius:999px; font-size:12px; }
        .low { background:#fee2e2; color:#991b1b; }
        .ok { background:#dcfce7; color:#065f46; }
        form.inline { display:flex; gap:8px; flex-wrap:wrap; margin-top:8px; }
        input, select { padding:8px 10px; border:1px solid #e5e7eb; border-radius:8px; }
        button { padding:8px 12px; border:0; border-radius:8px; background:#1976d2; color:#fff; cursor:pointer; }
        .muted { color:#64748b; font-size:12px; }
        .pill { border:1px solid #e5e7eb; border-radius:999px; padding:4px 8px; }
        a { color:#1976d2; text-decoration:none; }
    </style>
</head>
<body>
    <header>
        <div><strong>Inventory</strong> <span class="muted">Branch ID <?= (int)$branchId ?></span></div>
        <div><a href="<?= site_url('logout') ?>">Logout</a></div>
    </header>
    <div class="container">
        <div class="grid">
            <div class="card">
                <div><strong>Low Stock</strong></div>
                <div class="muted">Items below minimum</div>
                <ul>
                    <?php foreach ($lowStock as $row): ?>
                        <li><?= esc($row->name) ?> (<?= esc($row->sku) ?>) — <?= (int)$row->quantity ?>/min <?= (int)$row->min_stock ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="card">
                <div><strong>Expiring Soon (7d)</strong></div>
                <ul>
                    <?php foreach ($expiringSoon as $e): ?>
                        <li><?= esc($e->name) ?> — <?= (int)$e->quantity ?> by <?= esc($e->expiry_date) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="card" style="margin-top:16px">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <strong>Current Inventory</strong>
                <form method="get" class="inline">
                    <label class="muted">Branch</label>
                    <input type="number" name="branch_id" value="<?= (int)$branchId ?>" min="1">
                    <button type="submit">Go</button>
                </form>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>SKU</th>
                        <th>Qty</th>
                        <th>Min</th>
                        <th>Status</th>
                        <th>Nearest Expiry</th>
                        <th>Receive</th>
                        <th>Adjust</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $row): $isLow = ((int)$row->quantity < (int)$row->min_stock); ?>
                        <tr>
                            <td><?= esc($row->name) ?></td>
                            <td><?= esc($row->sku) ?></td>
                            <td><?= (int)$row->quantity ?></td>
                            <td><?= (int)$row->min_stock ?></td>
                            <td><span class="badge <?= $isLow ? 'low' : 'ok' ?>"><?= $isLow ? 'LOW' : 'OK' ?></span></td>
                            <td><?= esc($row->nearest_expiry ?? '-') ?></td>
                            <td>
                                <form class="inline" method="post" action="<?= site_url('inventory/receive') ?>">
                                    <input type="hidden" name="branch_id" value="<?= (int)$branchId ?>">
                                    <input type="hidden" name="item_id" value="<?= (int)$row->id ?>">
                                    <input type="number" name="quantity" min="1" placeholder="qty" required>
                                    <input type="date" name="expiry_date" placeholder="expiry (opt)">
                                    <button type="submit">Receive</button>
                                </form>
                            </td>
                            <td>
                                <form class="inline" method="post" action="<?= site_url('inventory/adjust') ?>">
                                    <input type="hidden" name="branch_id" value="<?= (int)$branchId ?>">
                                    <input type="hidden" name="item_id" value="<?= (int)$row->id ?>">
                                    <input type="number" name="delta" placeholder="±" required>
                                    <button type="submit">Apply</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (session()->getFlashdata('error')): ?><div class="card" style="margin-top:12px;color:#991b1b;background:#fee2e2;border-color:#fecaca;"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
        <?php if (session()->getFlashdata('success')): ?><div class="card" style="margin-top:12px;color:#065f46;background:#dcfce7;border-color:#bbf7d0;"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
    </div>
</body>
</html>

