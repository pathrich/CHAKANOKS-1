<?php
$title = $title ?? 'Deliveries';
?>

<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1"><?= esc($title) ?></h1>
            <div class="text-muted">View and manage scheduled deliveries.</div>
        </div>
        <div>
            <a href="<?= site_url('deliveries/create') ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Schedule Delivery
            </a>
        </div>
    </div>

    <?php if (session('success')): ?>
        <div class="alert alert-success"><?= esc(session('success')) ?></div>
    <?php endif; ?>
    <?php if (session('error')): ?>
        <div class="alert alert-danger"><?= esc(session('error')) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Scheduled Deliveries</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($deliveries)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Reference</th>
                                <th>Driver</th>
                                <th>Vehicle</th>
                                <th>Scheduled</th>
                                <th>Expected</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($deliveries as $d): ?>
                            <tr>
                                <td><?= (int) $d['id'] ?></td>
                                <td>
                                    <div class="fw-bold"><?= esc($d['order_id'] ?? '-') ?></div>
                                    <div class="small text-muted"><?= esc($d['type'] ?? 'PO') ?></div>
                                </td>
                                <td><?= esc($d['driver_name'] ?? '') ?></td>
                                <td><?= esc($d['vehicle'] ?? '') ?></td>
                                <td><?= esc($d['scheduled_at'] ?? '') ?></td>
                                <td><?= esc($d['expected_delivered_at'] ?? '') ?></td>
                                <td><span class="badge bg-secondary"><?= esc($d['status'] ?? '') ?></span></td>
                                <td>
                                    <a href="<?= site_url('deliveries/edit/'.$d['id']) ?>" class="btn btn-sm btn-outline-primary">
                                        Edit
                                    </a>
                                    <a href="<?= site_url('deliveries/track/'.$d['id']) ?>" class="btn btn-sm btn-outline-secondary">
                                        Track
                                    </a>
                                    <?php if (($d['status'] ?? '') !== 'delivered'): ?>
                                        <form method="post" action="<?= site_url('deliveries/mark-delivered') ?>" class="d-inline-block ms-1">
                                            <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-primary">Mark Delivered</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-muted">No deliveries scheduled.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
