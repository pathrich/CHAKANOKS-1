<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h4 mb-3">My Transfers</h1>
    <p class="text-muted">Transfers where your branch is the sender or receiver.</p>

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
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted">No transfers found for your branch.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
