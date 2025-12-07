<?php
$title = 'Purchase Orders';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background: linear-gradient(135deg, #16a085, #1abc9c); box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .navbar-brand, .nav-link { color: white !important; }
        .status-badge { font-size: 0.85rem; font-weight: 600; padding: 0.35rem 0.75rem; }
        .card { border: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 8px; }
        .table-hover tbody tr:hover { background-color: #f8f9fa; }
        .btn-sm { font-size: 0.8rem; }
        .alert { border-radius: 8px; }
        .po-status { display: inline-block; padding: 0.4rem 0.8rem; border-radius: 4px; font-weight: 600; font-size: 0.85rem; }
        .status-created { background-color: #e8f4f8; color: #0c5460; }
        .status-confirmed { background-color: #d4edda; color: #155724; }
        .status-shipped { background-color: #fff3cd; color: #856404; }
        .status-delivered { background-color: #d1ecf1; color: #0c5460; }
        .status-changed { background-color: #f8d7da; color: #721c24; }
        .status-declined { background-color: #f5c6cb; color: #721c24; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">Purchase Orders</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= site_url('logout') ?>">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <h2 class="mb-4 text-dark">All Purchase Orders</h2>

        <?php if (empty($purchaseOrders)): ?>
            <div class="alert alert-info">No purchase orders found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>PO Number</th>
                            <th>Supply Req</th>
                            <th>Supplier</th>
                            <th>Total Items</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Tracking</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($purchaseOrders as $po): ?>
                            <?php $statusClass = 'status-' . strtolower(str_replace('_', '-', $po['status'])); ?>
                            <tr>
                                <td><strong><?= esc($po['po_number']) ?></strong></td>
                                <td>
                                    <?php if ($po['supply_request_id']): ?>
                                        <a href="<?= site_url('supply-request/' . $po['supply_request_id']) ?>" class="btn-link">#<?= $po['supply_request_id'] ?></a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($po['supplier_id']): ?>
                                        <span class="badge bg-secondary"><?= $po['supplier_id'] ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $po['total_items'] ?></td>
                                <td><?= number_format($po['total_amount'], 2) ?></td>
                                <td>
                                    <span class="po-status <?= $statusClass ?>">
                                        <?= str_replace('_', ' ', $po['status']) ?>
                                    </span>
                                </td>
                                <td><?= esc($po['tracking_number'] ?? '—') ?></td>
                                <td><?= date('Y-m-d H:i', strtotime($po['created_at'])) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#poModal<?= $po['id'] ?>">Details</button>
                                </td>
                            </tr>

                            <!-- PO Details Modal -->
                            <div class="modal fade" id="poModal<?= $po['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">PO #<?= esc($po['po_number']) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <p><strong>Status:</strong> <span class="po-status <?= $statusClass ?>"><?= str_replace('_', ' ', $po['status']) ?></span></p>
                                                    <p><strong>Total Items:</strong> <?= $po['total_items'] ?></p>
                                                    <p><strong>Total Amount:</strong> <?= number_format($po['total_amount'], 2) ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Created:</strong> <?= date('Y-m-d H:i', strtotime($po['created_at'])) ?></p>
                                                    <?php if ($po['tracking_number']): ?>
                                                        <p><strong>Tracking:</strong> <?= esc($po['tracking_number']) ?></p>
                                                    <?php endif; ?>
                                                    <?php if ($po['notes']): ?>
                                                        <p><strong>Notes:</strong> <?= esc($po['notes']) ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <hr>

                                            <h6 class="mb-3">Items in this PO:</h6>
                                            <table class="table table-sm">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Item</th>
                                                        <th>Qty</th>
                                                        <th>Unit Price</th>
                                                        <th>Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $db = db_connect();
                                                    $items = $db->table('purchase_order_items')
                                                                ->select('poi.*, it.name')
                                                                ->from('purchase_order_items as poi')
                                                                ->join('items as it', 'it.id = poi.item_id')
                                                                ->where('poi.purchase_order_id', $po['id'])
                                                                ->get()->getResultArray();
                                                    ?>
                                                    <?php foreach ($items as $item): ?>
                                                        <tr>
                                                            <td><?= esc($item['name']) ?></td>
                                                            <td><?= $item['quantity'] ?></td>
                                                            <td><?= number_format($item['unit_price'], 2) ?></td>
                                                            <td><?= number_format($item['subtotal'], 2) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>

                                            <hr>

                                            <!-- Admin Actions -->
                                            <?php if ($po['status'] === 'SHIPPED'): ?>
                                                <div class="alert alert-info">
                                                    <p>This order has been shipped. Mark it as delivered when received?</p>
                                                    <button class="btn btn-success btn-sm" onclick="markDelivered(<?= $po['id'] ?>)">Mark as Delivered</button>
                                                </div>
                                            <?php elseif ($po['status'] === 'SUPPLIER_DECLINED'): ?>
                                                <div class="alert alert-warning">
                                                    <p>Supplier declined this order. Please reassign to another supplier.</p>
                                                    <button class="btn btn-warning btn-sm" onclick="reassignSupplier(<?= $po['id'] ?>)">Reassign Supplier</button>
                                                </div>
                                            <?php elseif ($po['status'] === 'SUPPLIER_REQUESTED_CHANGES'): ?>
                                                <div class="alert alert-warning">
                                                    <p>Supplier requested changes. Please review and take action.</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function markDelivered(poId) {
            if (confirm('Mark this PO as delivered? Inventory will be updated.')) {
                fetch('<?= site_url('purchase-order/mark-delivered') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ po_id: poId })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('PO marked as delivered. Inventory updated.');
                        location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
            }
        }

        function reassignSupplier(poId) {
            alert('Reassign supplier feature coming soon.');
        }
    </script>
</body>
</html>
