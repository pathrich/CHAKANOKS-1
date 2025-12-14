<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h4 mb-3">Inter-Branch Transfers</h1>
    <p class="text-muted">Central Office view of all transfer requests between branches.</p>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Item</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Requested At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($transfers)): ?>
                        <?php foreach ($transfers as $t): ?>
                            <tr>
                                <td><?= (int)$t->id ?></td>
                                <td><?= esc($t->item_name) ?> <br><small class="text-muted"><?= esc($t->sku) ?></small></td>
                                <td><?= esc($t->from_branch) ?> (ID <?= (int)$t->from_branch_id ?>)</td>
                                <td><?= esc($t->to_branch) ?> (ID <?= (int)$t->to_branch_id ?>)</td>
                                <td><?= (int)$t->quantity ?></td>
                                <td>
                                    <?php $status = $t->status; $badge = 'secondary';
                                        if ($status === 'Requested') $badge = 'warning';
                                        elseif ($status === 'Approved') $badge = 'success';
                                        elseif ($status === 'Rejected') $badge = 'danger';
                                        elseif ($status === 'Completed') $badge = 'primary';
                                    ?>
                                    <span class="badge bg-<?= $badge ?>"><?= esc($status) ?></span>
                                </td>
                                <td><?= esc($t->created_at) ?></td>
                                <td>
                                    <?php if ($t->status === 'Requested'): ?>
                                        <form method="post" action="<?= site_url('transfers/approve') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= (int)$t->id ?>">
                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                        <form method="post" action="<?= site_url('transfers/reject') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= (int)$t->id ?>">
                                            <input type="hidden" name="reason" value="Rejected by admin">
                                            <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">No actions</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted">No transfers found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
