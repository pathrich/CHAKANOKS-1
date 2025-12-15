<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h4 mb-0">My Activity Logs</h2>
                <small class="text-muted">A history of actions performed under your account.</small>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th style="width: 25%">When</th>
                            <th style="width: 25%">Action</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">No activity recorded for your account yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td class="small text-muted">
                                        <?= $log['created_at'] ? date('M d, Y H:i', strtotime($log['created_at'])) : '-' ?>
                                    </td>
                                    <td class="fw-semibold">
                                        <?= esc($log['action'] ?? '-') ?>
                                    </td>
                                    <td class="small">
                                        <?= esc($log['details'] ?? '') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
