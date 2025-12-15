<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h4 mb-0">Account Settings</h2>
                <small class="text-muted">Manage your profile information and password.</small>
            </div>
            <div class="text-end small text-muted">
                <?php if (!empty($user['created_at'])): ?>
                    <div>Member since: <strong><?= date('M d, Y', strtotime($user['created_at'])) ?></strong></div>
                <?php endif; ?>
                <?php if (!empty($user['updated_at'])): ?>
                    <div>Last updated: <strong><?= date('M d, Y H:i', strtotime($user['updated_at'])) ?></strong></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="mb-1">Profile Details</h5>
                    <small class="text-muted">These details are used across the system.</small>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger small mb-3">
                            <?= esc(session()->getFlashdata('error')) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success small mb-3">
                            <?= esc(session()->getFlashdata('success')) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?= site_url('profile/update') ?>">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" value="<?= esc(old('username', $user['username'])) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?= esc(old('full_name', $user['full_name'] ?? '')) ?>">
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="mb-1">Change Password</h5>
                    <small class="text-muted">Choose a strong password that you do not use elsewhere.</small>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= site_url('profile/change-password') ?>">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-outline-warning">
                                <i class="fas fa-key"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <h6 class="mb-1">Account Overview</h6>
                    <small class="text-muted">Quick information about your account.</small>
                </div>
                <div class="card-body small">
                    <dl class="row mb-0">
                        <dt class="col-5 text-muted">Username</dt>
                        <dd class="col-7 mb-2 fw-semibold"><?= esc($user['username']) ?></dd>

                        <dt class="col-5 text-muted">Display name</dt>
                        <dd class="col-7 mb-2 fw-semibold"><?= esc($user['full_name'] ?: $user['username']) ?></dd>

                        <dt class="col-5 text-muted">Status</dt>
                        <dd class="col-7 mb-2"><span class="badge bg-success">Active</span></dd>

                        <?php if (!empty($user['created_at'])): ?>
                            <dt class="col-5 text-muted">Created</dt>
                            <dd class="col-7 mb-2 fw-semibold"><?= date('M d, Y H:i', strtotime($user['created_at'])) ?></dd>
                        <?php endif; ?>

                        <?php if (!empty($user['updated_at'])): ?>
                            <dt class="col-5 text-muted">Last change</dt>
                            <dd class="col-7 mb-2 fw-semibold"><?= date('M d, Y H:i', strtotime($user['updated_at'])) ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
