<?php
$title = 'Access Denied';
?>

<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1"><?= esc($title) ?></h1>
            <div class="text-muted">You are not authorized to access this dashboard.</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Access Restricted</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-warning mb-4">
                You do not have permission to access the dashboard. Please contact an administrator to grant you the necessary permissions.
            </div>

            <div class="row g-3">
                <div class="col-12 col-lg-7">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">Your Current Roles</h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($userRoles)): ?>
                                <ul class="mb-0">
                                    <?php foreach ($userRoles as $role): ?>
                                        <li><?= esc($role) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div class="text-muted">No roles assigned</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-5">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">Next Steps</h6>
                        </div>
                        <div class="card-body">
                            <div class="text-muted mb-2"><strong>Required Roles:</strong> central_admin, branch_manager</div>

                            <?php
                                $roles = $userRoles ?? [];

                                // Determine best "home" route based on current role
                                if (in_array('inventory_staff', $roles, true) || in_array('branch_manager', $roles, true) || in_array('central_admin', $roles, true)) {
                                    $primaryUrl  = site_url('inventory');
                                    $primaryText = 'Go to Inventory';
                                } elseif (in_array('logistics_coordinator', $roles, true)) {
                                    $primaryUrl  = site_url('deliveries');
                                    $primaryText = 'Go to Deliveries';
                                } elseif (in_array('supplier', $roles, true) || in_array('franchise', $roles, true)) {
                                    $primaryUrl  = site_url('purchase-order');
                                    $primaryText = 'Go to Purchase Orders';
                                } else {
                                    $primaryUrl  = site_url('dashboard');
                                    $primaryText = 'Go to Dashboard';
                                }
                            ?>

                            <div class="d-flex gap-2">
                                <a href="<?= $primaryUrl ?>" class="btn btn-primary"><?= esc($primaryText) ?></a>
                                <a href="<?= site_url('logout') ?>" class="btn btn-outline-secondary">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
