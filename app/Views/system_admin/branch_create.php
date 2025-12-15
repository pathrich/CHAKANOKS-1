<?php
$title = $title ?? 'Create Branch';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?></title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;background:#f5f5f5;color:#333}
        .navbar{background:#16a085;color:#fff;padding:1rem 2rem;display:flex;justify-content:space-between;align-items:center}
        .container{max-width:800px;margin:2rem auto;padding:0 1rem}
        .card{background:#fff;padding:1rem;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.05)}
        .form-group{margin-bottom:1rem}
        label{display:block;margin-bottom:.25rem;font-weight:600}
        input, select, textarea{width:100%;padding:.5rem;border-radius:4px;border:1px solid #ccc}
        .btn{display:inline-block;padding:.45rem .8rem;border-radius:6px;text-decoration:none;color:#fff;background:#16a085;border:none;cursor:pointer}
        .btn-secondary{background:#95a5a6}
        .alert{padding:.75rem 1rem;border-radius:6px;margin-bottom:1rem}
        .alert-error{background:#e74c3c;color:#fff}
    </style>
</head>
<body>
    <nav class="navbar">
        <div><strong><?= esc($title) ?></strong></div>
        <div>
            <a href="<?= site_url('system-admin/branches') ?>" style="color:white;text-decoration:none;margin-right:1rem">Back</a>
            <a href="<?= site_url('logout') ?>" style="color:white;text-decoration:none">Logout</a>
        </div>
    </nav>
    <div class="container">
        <div class="card">
            <h3 style="margin-bottom:1rem">Add New Branch</h3>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= site_url('system-admin/branches/store') ?>">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label>Branch Name</label>
                    <input type="text" name="name" value="<?= old('name') ?>" required>
                </div>

                <div class="form-group">
                    <label>Branch Code</label>
                    <input type="text" name="code" value="<?= old('code') ?>" required>
                </div>

                <div class="form-group">
                    <label>Branch Address</label>
                    <textarea name="address" rows="2"><?= old('address') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" name="contact_number" value="<?= old('contact_number') ?>">
                </div>

                <div class="form-group">
                    <label>Branch Status</label>
                    <select name="status">
                        <?php $oldStatus = old('status') ?: 'Active'; ?>
                        <option value="Active" <?= $oldStatus === 'Active' ? 'selected' : '' ?>>Active</option>
                        <option value="Inactive" <?= $oldStatus === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <div style="margin-top:1rem;display:flex;gap:.5rem">
                    <button type="submit" class="btn">Save Branch</button>
                    <a href="<?= site_url('system-admin/branches') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
