<?php
$title = 'Inventory';
?>
<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">Inventory</h1>
            <div class="text-muted">Branch ID <?= (int) $branchId ?></div>
        </div>
        <div class="d-flex gap-2 align-items-end flex-wrap">
            <form method="get" class="d-flex align-items-center gap-2">
                <div class="text-muted small">Branch</div>
                <input type="number" name="branch_id" value="<?= (int) $branchId ?>" min="1" class="form-control form-control-sm" style="width: 90px;">
                <button type="submit" class="btn btn-sm btn-primary">Go</button>
            </form>
            <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2">
                <div class="text-muted small">Scan / Search SKU or Barcode</div>
                <div class="input-group input-group-sm">
                    <input type="text" id="barcodeSearch" class="form-control" placeholder="SKU or barcode">
                    <button type="button" class="btn btn-outline-primary" onclick="scanBarcode()">Search</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Low Stock</h5>
                    <div class="text-muted small">Items below minimum</div>
                </div>
                <div class="card-body">
                    <?php if (! empty($lowStock)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($lowStock as $row): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold"><?= esc($row->name) ?> <span class="text-muted">(<?= esc($row->sku) ?>)</span></div>
                                        <div class="text-muted small">Current: <?= (int) $row->quantity ?> / Min: <?= (int) $row->min_stock ?></div>
                                    </div>
                                    <form method="post" action="<?= site_url('inventory/ack-low') ?>" class="ms-3">
                                        <input type="hidden" name="branch_id" value="<?= (int) $branchId ?>">
                                        <input type="hidden" name="item_id" value="<?= (int) $row->id ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Acknowledge</button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-muted">No low stock alerts at the moment.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Expiring Soon (7 days)</h5>
                </div>
                <div class="card-body">
                    <?php if (! empty($expiringSoon)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($expiringSoon as $e): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold"><?= esc($e->name) ?></div>
                                        <div class="text-muted small">Qty: <?= (int) $e->quantity ?></div>
                                    </div>
                                    <span class="badge bg-warning text-dark">Expires <?= esc($e->expiry_date) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-muted">No items expiring in the next 7 days.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Current Inventory</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>SKU</th>
                            <th>Qty</th>
                            <th>Min</th>
                            <th>Status</th>
                            <th>Nearest Expiry</th>
                            <th style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $row): $isLow = ((int) $row->quantity < (int) $row->min_stock); $rowId = (int) $row->id; ?>
                            <tr data-sku="<?= esc($row->sku) ?>" data-barcode="<?= esc($row->barcode ?? '') ?>">
                                <td><?= esc($row->name) ?></td>
                                <td><?= esc($row->sku) ?></td>
                                <td><?= (int) $row->quantity ?></td>
                                <td><?= (int) $row->min_stock ?></td>
                                <td>
                                    <?php if ($isLow): ?>
                                        <span class="badge bg-warning text-dark">LOW</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">OK</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($row->nearest_expiry ?? '-') ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#inv-actions-<?= $rowId ?>" aria-expanded="false" aria-controls="inv-actions-<?= $rowId ?>">
                                        Manage
                                    </button>
                                </td>
                            </tr>
                            <tr class="collapse" id="inv-actions-<?= $rowId ?>">
                                <td colspan="7" class="bg-light">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-6 col-lg-3">
                                            <h6 class="small text-uppercase text-muted mb-2">Receive</h6>
                                            <form class="row g-1" method="post" action="<?= site_url('inventory/receive') ?>">
                                                <input type="hidden" name="branch_id" value="<?= (int) $branchId ?>">
                                                <input type="hidden" name="item_id" value="<?= $rowId ?>">
                                                <div class="col-5">
                                                    <input type="number" name="quantity" min="1" placeholder="Qty" required class="form-control form-control-sm">
                                                </div>
                                                <div class="col-7">
                                                    <input type="date" name="expiry_date" class="form-control form-control-sm">
                                                </div>
                                                <div class="col-12 d-grid mt-1">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">Receive</button>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-3">
                                            <h6 class="small text-uppercase text-muted mb-2">Adjust</h6>
                                            <form class="row g-1" method="post" action="<?= site_url('inventory/adjust') ?>">
                                                <input type="hidden" name="branch_id" value="<?= (int) $branchId ?>">
                                                <input type="hidden" name="item_id" value="<?= $rowId ?>">
                                                <div class="col-7">
                                                    <input type="number" name="delta" placeholder="±" required class="form-control form-control-sm">
                                                </div>
                                                <div class="col-5 d-grid">
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Apply</button>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-3">
                                            <h6 class="small text-uppercase text-muted mb-2">Quality &amp; History</h6>
                                            <form class="row g-1 mb-1" method="post" action="<?= site_url('inventory/expired') ?>">
                                                <input type="hidden" name="branch_id" value="<?= (int) $branchId ?>">
                                                <input type="hidden" name="item_id" value="<?= $rowId ?>">
                                                <div class="col-7">
                                                    <input type="number" name="quantity" placeholder="Expired" min="1" class="form-control form-control-sm">
                                                </div>
                                                <div class="col-5 d-grid">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Expired</button>
                                                </div>
                                            </form>
                                            <form class="row g-1 mb-1" method="post" action="<?= site_url('inventory/damaged') ?>">
                                                <input type="hidden" name="branch_id" value="<?= (int) $branchId ?>">
                                                <input type="hidden" name="item_id" value="<?= $rowId ?>">
                                                <div class="col-7">
                                                    <input type="number" name="quantity" placeholder="Damaged" min="1" class="form-control form-control-sm">
                                                </div>
                                                <div class="col-5 d-grid">
                                                    <button type="submit" class="btn btn-sm btn-outline-warning">Damaged</button>
                                                </div>
                                            </form>
                                            <div class="small mt-1">
                                                <a href="<?= site_url('inventory/history/' . $rowId . '?branch_id=' . (int) $branchId) ?>">View History</a>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-3">
                                            <h6 class="small text-uppercase text-muted mb-2">Transfer</h6>
                                            <form class="row g-1" method="post" action="<?= site_url('inventory/transfer-request') ?>">
                                                <input type="hidden" name="from_branch_id" value="<?= (int) $branchId ?>">
                                                <input type="hidden" name="item_id" value="<?= $rowId ?>">
                                                <div class="col-4">
                                                    <input type="number" name="to_branch_id" min="1" placeholder="To" required class="form-control form-control-sm">
                                                </div>
                                                <div class="col-3">
                                                    <input type="number" name="quantity" min="1" placeholder="Qty" required class="form-control form-control-sm">
                                                </div>
                                                <div class="col-5 d-grid mt-1 mt-md-0">
                                                    <input type="text" name="reason" placeholder="Reason (opt)" class="form-control form-control-sm mb-1">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">Request</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger mt-3 mb-0"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success mt-3 mb-0"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
    function scanBarcode() {
        const value = (document.getElementById('barcodeSearch').value || '').trim().toLowerCase();
        if (!value) return;

        const rows = document.querySelectorAll('tbody tr');
        let found = false;
        rows.forEach(row => {
            row.style.outline = '';
            const sku = (row.getAttribute('data-sku') || '').toLowerCase();
            const barcode = (row.getAttribute('data-barcode') || '').toLowerCase();
            if (!found && (sku.includes(value) || barcode.includes(value))) {
                found = true;
                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                row.style.outline = '2px solid #1976d2';
            }
        });

        if (!found) {
            alert('No matching item found for: ' + value);
        }
    }
</script>
<?= $this->endSection() ?>
