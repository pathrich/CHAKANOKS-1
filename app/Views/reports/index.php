<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="h4">Reports</h2>
            <p class="text-muted small mb-0">Overview of key metrics for your supply chain system.</p>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Total Branches</div>
                    <div class="fs-4 fw-semibold mb-1"><?= esc($totalBranches ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Total Users</div>
                    <div class="fs-4 fw-semibold mb-1"><?= esc($totalUsers ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Total Items</div>
                    <div class="fs-4 fw-semibold mb-1"><?= $totalItems !== null ? esc($totalItems) : '<span class="text-muted">N/A</span>' ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Pending Supply Requests</div>
                    <div class="fs-6 fw-semibold mb-1"><?= $pendingSupplyRequests !== null ? esc($pendingSupplyRequests) : '<span class="text-muted">N/A</span>' ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="text-muted small mb-2">More detailed reports can be added here later (inventory, orders, deliveries, etc.), reusing this layout.</div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
