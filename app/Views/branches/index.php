<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h1 class="h4">Branches Management</h1>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Add New Branch</h5>
                    <form method="post" action="<?= site_url('branches/store') ?>">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Branch Name</label>
                            <input type="text" name="name" class="form-control" value="<?= old('name') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Branch Code</label>
                            <input type="text" name="code" class="form-control" value="<?= old('code') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Branch Address</label>
                            <textarea name="address" class="form-control" rows="2"><?= old('address') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contact Number</label>
                            <input type="text" name="contact_number" class="form-control" value="<?= old('contact_number') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Branch Status</label>
                            <select name="status" class="form-select">
                                <?php $oldStatus = old('status') ?: 'Active'; ?>
                                <option value="Active" <?= $oldStatus === 'Active' ? 'selected' : '' ?>>Active</option>
                                <option value="Inactive" <?= $oldStatus === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Branch</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Existing Branches</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Address</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($branches)): ?>
                                    <?php foreach ($branches as $b): ?>
                                        <tr>
                                            <td><?= esc($b['name']) ?></td>
                                            <td><?= esc($b['code']) ?></td>
                                            <td><?= esc($b['address'] ?? $b['city'] ?? '-') ?></td>
                                            <td><?= esc($b['contact_number'] ?? '-') ?></td>
                                            <td>
                                                <?php $status = strtolower($b['status'] ?? 'active'); ?>
                                                <?php if ($status === 'inactive'): ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No branches found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
