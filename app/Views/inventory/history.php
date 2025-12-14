<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory History - <?= esc($item->name ?? 'Item') ?></title>
    <style>
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background:#f6f7fb; margin:0; }
        header { background:#fff; padding:16px 24px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; }
        .container { padding:24px; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:8px 10px; border-bottom:1px solid #e5e7eb; font-size:14px; text-align:left; }
        th { background:#f8fafc; font-weight:600; }
        .badge { padding:2px 8px; border-radius:999px; font-size:12px; }
        .in { background:#dcfce7; color:#166534; }
        .out { background:#fee2e2; color:#991b1b; }
        a { color:#1976d2; text-decoration:none; }
    </style>
</head>
<body>
    <header>
        <div>
            <strong>Inventory History</strong>
            <span>Branch <?= (int)$branchId ?> 
                
            </span>
        </div>
        <div>
            <a href="<?= site_url('inventory?branch_id=' . (int)$branchId) ?>">Back to Inventory</a>
        </div>
    </header>
    <div class="container">
        <h2><?= esc($item->name ?? 'Item') ?> (<?= esc($item->sku ?? '') ?>)</h2>

        <table>
            <thead>
                <tr>
                    <th>Date/Time</th>
                    <th>Quantity Change</th>
                    <th>Expiry Date</th>
                    <th>Direction</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($movements)): ?>
                    <?php foreach ($movements as $m): $q = (int)$m->quantity; ?>
                        <tr>
                            <td><?= esc($m->created_at) ?></td>
                            <td><?= $q ?></td>
                            <td><?= esc($m->expiry_date ?? '-') ?></td>
                            <td>
                                <?php if ($q >= 0): ?>
                                    <span class="badge in">IN</span>
                                <?php else: ?>
                                    <span class="badge out">OUT</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4">No history found for this item.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
