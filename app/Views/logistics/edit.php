<?php
$title = $title ?? 'Update Delivery Schedule';
$delivery = $delivery ?? [];

$dtLocal = function ($v) {
    if (!$v) return '';
    $ts = strtotime($v);
    if (!$ts) return '';
    return date('Y-m-d\TH:i', $ts);
};
?>

<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1"><?= esc($title) ?></h1>
            <div class="text-muted">Assign driver, schedule and expected delivery.</div>
        </div>
        <div>
            <a href="<?= site_url('deliveries') ?>" class="btn btn-outline-secondary">Back to Deliveries</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Update Delivery #<?= esc($delivery['id'] ?? '') ?></h5>
            <div class="text-muted small">
                <?= esc($delivery['type'] ?? 'PO') ?>
                <?php if (!empty($delivery['current_location'])): ?>
                    - <?= esc($delivery['current_location']) ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <form method="post" action="<?= site_url('deliveries/update/' . (int)($delivery['id'] ?? 0)) ?>">
                <div class="row g-3">
                    <div class="col-12">
                        <label for="order_id" class="form-label">Order ID</label>
                        <?php if (($delivery['type'] ?? '') === 'PO'): ?>
                            <input id="order_id" type="text" class="form-control" value="<?= esc($delivery['order_id'] ?? '') ?>" disabled />
                            <div class="form-text">This is a PO-linked delivery. Do not set an Order ID here.</div>
                        <?php else: ?>
                            <input id="order_id" type="text" class="form-control" name="order_id" value="<?= esc($delivery['order_id'] ?? '') ?>" />
                            <div class="form-text">Leave blank unless this delivery is for an Order.</div>
                        <?php endif; ?>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="driver_name" class="form-label">Driver Name</label>
                        <input id="driver_name" type="text" class="form-control" name="driver_name" value="<?= esc($delivery['driver_name'] ?? '') ?>" />
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="vehicle" class="form-label">Vehicle</label>
                        <input id="vehicle" type="text" class="form-control" name="vehicle" value="<?= esc($delivery['vehicle'] ?? '') ?>" />
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="scheduled_at" class="form-label">Scheduled At</label>
                        <input id="scheduled_at" type="datetime-local" class="form-control" name="scheduled_at" value="<?= esc($dtLocal($delivery['scheduled_at'] ?? null)) ?>" />
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="expected_delivered_at" class="form-label">Expected Delivered At</label>
                        <input id="expected_delivered_at" type="datetime-local" class="form-control" name="expected_delivered_at" value="<?= esc($dtLocal($delivery['expected_delivered_at'] ?? null)) ?>" />
                    </div>

                    <div class="col-12">
                        <label for="route" class="form-label">Route (JSON)</label>
                        <textarea id="route" class="form-control" name="route" rows="6" placeholder='[{"lat":...,"lng":...}, ...]'><?= esc($delivery['route'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-primary" type="submit">Save Changes</button>
                    <a href="<?= site_url('deliveries') ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
