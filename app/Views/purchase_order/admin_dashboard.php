<?php
$title = 'Purchase Orders';
?>

<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<style>
    .po-status { display: inline-block; padding: 0.35rem 0.75rem; border-radius: 999px; font-weight: 700; font-size: 0.80rem; }
    .status-po-created { background-color: rgba(37, 99, 235, 0.12); color: #1d4ed8; }
    .status-supplier-confirmed { background-color: rgba(22, 163, 74, 0.14); color: #166534; }
    .status-shipped { background-color: rgba(245, 158, 11, 0.18); color: #92400e; }
    .status-delivered { background-color: rgba(14, 165, 233, 0.14); color: #075985; }
    .status-supplier-requested-changes { background-color: rgba(234, 179, 8, 0.18); color: #854d0e; }
    .status-supplier-declined { background-color: rgba(220, 38, 38, 0.12); color: #991b1b; }
</style>

<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1">All Purchase Orders</h1>
            <div class="text-muted">Monitor supplier actions, shipping, and delivery status.</div>
        </div>
    </div>

        <?php if (empty($purchaseOrders)): ?>
            <div class="card">
                <div class="card-body">
                    <div class="text-muted">No purchase orders found.</div>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
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
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#poModal<?= $po['id'] ?>">Details</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                    </div>
                </div>
            </div>

            <?php foreach ($purchaseOrders as $po): ?>
                <?php $statusClass = 'status-' . strtolower(str_replace('_', '-', $po['status'])); ?>
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
        <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function markDelivered(poId) {
        if (confirm('Mark this PO as delivered? Inventory will be updated.')) {
            fetch('<?= site_url('purchase-order/mark-delivered') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ po_id: poId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('PO marked as delivered. Inventory updated.');
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed'));
                }
            });
        }
    }

    function reassignSupplier(poId) {
        alert('Reassign supplier feature coming soon.');
    }
</script>
<?= $this->endSection() ?>
