<?php
$title = $title ?? 'Schedule Delivery';
?>

<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1"><?= esc($title) ?></h1>
            <div class="text-muted">Create a new delivery schedule.</div>
        </div>
        <div>
            <a href="<?= site_url('deliveries') ?>" class="btn btn-outline-secondary">Back to Deliveries</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Schedule Delivery</h5>
        </div>
        <div class="card-body">
            <form method="post" action="<?= site_url('deliveries/store') ?>">
                <div class="row g-3">
                    <div class="col-12">
                        <label for="order_id" class="form-label">Order ID</label>
                        <input id="order_id" type="text" class="form-control" name="order_id" value="<?= set_value('order_id') ?>" />
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="driver_name" class="form-label">Driver Name</label>
                        <input id="driver_name" type="text" class="form-control" name="driver_name" value="<?= set_value('driver_name') ?>" />
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="vehicle" class="form-label">Vehicle</label>
                        <input id="vehicle" type="text" class="form-control" name="vehicle" value="<?= set_value('vehicle') ?>" />
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="scheduled_at" class="form-label">Scheduled At</label>
                        <input id="scheduled_at" type="datetime-local" class="form-control" name="scheduled_at" value="<?= set_value('scheduled_at') ?>" />
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="expected_delivered_at" class="form-label">Expected Delivered At</label>
                        <input id="expected_delivered_at" type="datetime-local" class="form-control" name="expected_delivered_at" value="<?= set_value('expected_delivered_at') ?>" />
                    </div>
                    <div class="col-12">
                        <label for="route" class="form-label">Route (JSON)</label>
                        <textarea id="route" class="form-control" name="route" rows="6" placeholder='[{"lat":...,"lng":...}, ...]'><?= set_value('route') ?></textarea>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-primary" type="submit">Schedule</button>
                    <a href="<?= site_url('deliveries') ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
