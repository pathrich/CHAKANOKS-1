<?php
$title = $title ?? 'User Management';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?></title>
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#f4f6f8; color:#2c3e50; }
        .page-shell { max-width:1180px; margin:2rem auto; padding:0 1.5rem 2rem; }
        .page-header { margin-bottom:1.5rem; }
        .page-title { font-size:1.6rem; font-weight:600; margin-bottom:.25rem; }
        .page-subtitle { font-size:.9rem; color:#7f8c8d; }

        .tabs { display:flex; border-bottom:1px solid #e1e5eb; margin-bottom:1.25rem; }
        .tab { padding:.75rem 1.5rem; cursor:pointer; font-size:.95rem; border-bottom:3px solid transparent; color:#7f8c8d; text-decoration:none; }
        .tab.active { color:#16a085; border-bottom-color:#16a085; background:#ecfdf8; }

        .section { background:#ffffff; border-radius:10px; box-shadow:0 2px 8px rgba(15,23,42,0.04); padding:1.25rem 1.5rem; }
        .section-title { font-size:1rem; font-weight:600; margin-bottom:.5rem; }
        .section-subtitle { font-size:.85rem; color:#7f8c8d; margin-bottom:1rem; }

        table { width:100%; border-collapse:collapse; font-size:.9rem; }
        thead th { padding:.6rem .75rem; text-align:left; background:#f3f5f7; color:#54616f; border-bottom:1px solid #e1e5eb; }
        tbody td { padding:.55rem .75rem; border-bottom:1px solid #edf0f5; }
        tbody tr:hover { background:#f8fffd; }

        .badge-role { display:inline-block; padding:.1rem .45rem; font-size:.7rem; border-radius:999px; background:#ecf0f1; margin-right:.25rem; }
        .btn-link { display:inline-block; padding:.35rem .7rem; border-radius:6px; text-decoration:none; font-size:.8rem; background:#16a085; color:#ffffff; }

        .toolbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; gap:.75rem; flex-wrap:wrap; }
        .search-input { padding:.45rem .6rem; border-radius:6px; border:1px solid #d0d7e2; min-width:220px; font-size:.85rem; }
    </style>
</head>
<body>
    <div class="page-shell">
        <header class="page-header">
            <div class="page-title">Settings &amp; Users</div>
            <div class="page-subtitle">Review and manage user accounts and access.</div>
        </header>

        <nav class="tabs" aria-label="Settings navigation">
            <a class="tab" href="<?= site_url('system-admin') ?>">System Settings</a>
            <span class="tab active">User Management</span>
        </nav>

        <section class="section">
            <div class="section-title">User Accounts</div>
            <div class="section-subtitle">All application users and their assigned roles.</div>

            <div class="toolbar">
                <div class="toolbar-left">
                    <!-- Placeholder for future filters (role, status) -->
                </div>
                <div class="toolbar-right">
                    <input type="text" id="userSearch" class="search-input" placeholder="Search by name, username, or role...">
                </div>
            </div>

            <div class="table-wrapper">
                <table id="usersTable">
                    <thead>
                        <tr>
                            <th style="width: 18%">Name</th>
                            <th style="width: 17%">Username</th>
                            <th style="width: 25%">Role(s)</th>
                            <th style="width: 10%">Status</th>
                            <th style="width: 15%">Date Created</th>
                            <th style="width: 15%">Last Activity</th>
                            <th style="width: 10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?= esc($u['full_name'] ?: $u['username']) ?></td>
                                    <td><?= esc($u['username']) ?></td>
                                    <td>
                                        <?php if (!empty($u['roles'])): ?>
                                            <?php foreach (explode(',', $u['roles']) as $role): ?>
                                                <span class="badge-role"><?= esc(trim($role)) ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="badge-role">No role</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>Active</td>
                                    <td>
                                        <?= !empty($u['created_at']) ? date('M d, Y', strtotime($u['created_at'])) : '-' ?>
                                    </td>
                                    <td>
                                        <?= !empty($u['updated_at']) ? date('M d, Y H:i', strtotime($u['updated_at'])) : '-' ?>
                                    </td>
                                    <td>
                                        <a class="btn-link" href="<?= site_url('system-admin/users/edit/'.$u['id']) ?>">View / Edit</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align:center; padding:1.5rem 0;" class="text-muted">No users found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        (function() {
            const input = document.getElementById('userSearch');
            const table = document.getElementById('usersTable');
            if (!input || !table) return;

            input.addEventListener('input', function() {
                const term = this.value.toLowerCase();
                const rows = table.querySelectorAll('tbody tr');

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(term) ? '' : 'none';
                });
            });
        })();
    </script>
</body>
</html>
