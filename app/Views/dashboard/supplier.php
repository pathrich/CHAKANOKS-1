<?php
$title = 'Supplier Dashboard';
?>

<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1"><?= esc($title) ?></h1>
            <div class="text-muted">Welcome back, <?= esc(session('user_full_name') ?? 'Supplier') ?>!</div>
        </div>
    </div>

    <!-- Notifications Section -->
    <div class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Notifications</h5>
            <span class="badge bg-primary" id="supplierNotificationsBadge" style="display:none;">0</span>
        </div>
        <div class="card-body" id="supplierNotificationsContainer">
            <div class="text-muted">Loading notifications...</div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Total Items</div>
                    <div class="fs-3 fw-bold"><?= (int) ($totalItems ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Recent Activities</div>
                    <div class="fs-3 fw-bold"><?= (int) (!empty($recentActivity) ? count($recentActivity) : 0) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Recent Activity</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($recentActivity)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Action</th>
                                <th>Details</th>
                                <th>Date/Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentActivity as $log): ?>
                                <tr>
                                    <td><?= esc($log['full_name'] ?? 'N/A') ?></td>
                                    <td><?= esc($log['action']) ?></td>
                                    <td><?= esc($log['details'] ?? '-') ?></td>
                                    <td><?= esc($log['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-muted">No recent activity logged yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('<?= site_url('api/notifications') ?>?unreadOnly=1&limit=5', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('supplierNotificationsContainer');
        const badge = document.getElementById('supplierNotificationsBadge');

        if (!container) return;

        const notifications = data.notifications || [];
        const unreadCount = data.unreadCount || 0;

        if (badge && unreadCount > 0) {
            badge.textContent = unreadCount;
            badge.style.display = 'inline-block';
        }

        if (notifications.length === 0) {
            container.innerHTML = '<div class="text-muted">No notifications at this time.</div>';
            return;
        }

        let html = '<ul class="list-group list-group-flush">';
        notifications.forEach(n => {
            const created = n.created_at ? new Date(n.created_at).toLocaleString() : '';
            html += '<li class="list-group-item">'
                 +  '<div class="fw-bold">' + (n.title ?? 'Notification') + '</div>'
                 +  '<div class="small text-muted">' + (n.message ?? '') + '</div>'
                 +  '<div class="small text-muted mt-1">' + created + '</div>'
                 +  '</li>';
        });
        html += '</ul>';
        container.innerHTML = html;
    })
    .catch(() => {
        const container = document.getElementById('supplierNotificationsContainer');
        if (container) {
            container.innerHTML = '<div class="text-muted">Unable to load notifications.</div>';
        }
    });
});
</script>
<?= $this->endSection() ?>
