<?php
$title = 'Franchise Dashboard';
?>

<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1"><?= esc($title) ?></h1>
            <div class="text-muted">Welcome back, <?= esc(session('user_full_name') ?? 'Franchise') ?>!</div>
        </div>
    </div>

    <?php if (!empty($branch)): ?>
        <div class="card mb-4">
            <div class="card-body">
                <div><strong>Branch:</strong> <?= esc($branch->name) ?> (<?= esc($branch->code) ?>)</div>
                <?php if (!empty($branch->city)): ?>
                    <div><strong>City:</strong> <?= esc($branch->city) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase">Total Stock Items</div>
                        <div class="fs-3 fw-bold"><?= (int) ($itemCount ?? 0) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase">Total Units in Stock</div>
                        <div class="fs-3 fw-bold"><?= (int) ($totalStock ?? 0) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Recent Activity</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($recentActivity)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>Date/Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentActivity as $log): ?>
                                    <tr>
                                        <td><?= esc($log['full_name'] ?? 'N/A') ?></td>
                                        <td><?= esc($log['action']) ?></td>
                                        <td><?= esc($log['details'] ?? '-') ?></td>
                                        <td><?= esc($log['created_at']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-muted">No recent activity for your branch.</div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">Your account does not have a branch assigned. Please contact an administrator.</div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
