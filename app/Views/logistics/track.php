<?php
$title = $title ?? 'Track Delivery';
?>

<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1"><?= esc($title) ?></h1>
            <div class="text-muted">Delivery details and tracking information.</div>
        </div>
        <div>
            <a href="<?= site_url('deliveries') ?>" class="btn btn-outline-secondary">Back to Deliveries</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Delivery Details</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 text-muted">Delivery ID</dt>
                        <dd class="col-sm-7"><?= esc($delivery['id']) ?></dd>

                        <dt class="col-sm-5 text-muted">Order ID</dt>
                        <dd class="col-sm-7"><?= esc($delivery['order_id'] ?? '-') ?></dd>

                        <dt class="col-sm-5 text-muted">Type</dt>
                        <dd class="col-sm-7"><?= esc($delivery['type'] ?? 'PO') ?></dd>

                        <dt class="col-sm-5 text-muted">Driver</dt>
                        <dd class="col-sm-7"><?= esc($delivery['driver_name'] ?? '') ?></dd>

                        <dt class="col-sm-5 text-muted">Vehicle</dt>
                        <dd class="col-sm-7"><?= esc($delivery['vehicle'] ?? '') ?></dd>

                        <dt class="col-sm-5 text-muted">Status</dt>
                        <dd class="col-sm-7"><?= esc($delivery['status'] ?? '') ?></dd>

                        <dt class="col-sm-5 text-muted">Scheduled At</dt>
                        <dd class="col-sm-7"><?= esc($delivery['scheduled_at'] ?? '') ?></dd>

                        <dt class="col-sm-5 text-muted">Expected Delivered At</dt>
                        <dd class="col-sm-7"><?= esc($delivery['expected_delivered_at'] ?? '') ?></dd>

                        <dt class="col-sm-5 text-muted">Current Location</dt>
                        <dd class="col-sm-7"><?= esc($delivery['current_location'] ?? '') ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Route</h5>
                </div>
                <div class="card-body">
                    <pre class="mb-0" style="white-space: pre-wrap;"><?= esc($delivery['route'] ?? '') ?></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
