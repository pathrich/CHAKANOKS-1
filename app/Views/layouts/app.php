<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Supply Chain Management' ?> - CHAKANOKS</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #f59e0b;
            --secondary-color: #7c2d12;
            --success-color: #16a34a;
            --danger-color: #dc2626;
            --warning-color: #f59e0b;
            --info-color: #2563eb;
            --light-color: #fff7ed;
            --dark-color: #1f2937;
            --surface-color: #ffffff;
            --border-color: rgba(124, 45, 18, 0.12);
            --shadow-sm: 0 2px 8px rgba(31, 41, 55, 0.08);
            --shadow-md: 0 10px 30px rgba(31, 41, 55, 0.12);
            --radius: 14px;
        }

        body {
            font-family: 'Nunito', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(1200px 600px at 10% 0%, rgba(245, 158, 11, 0.20), transparent 60%),
                radial-gradient(900px 500px at 100% 10%, rgba(124, 45, 18, 0.14), transparent 55%),
                #fffaf2;
            color: var(--dark-color);
        }

        a {
            color: var(--secondary-color);
        }

        a:hover {
            color: #9a3412;
        }

        .navbar {
            background: linear-gradient(135deg, #b45309, var(--secondary-color));
            box-shadow: var(--shadow-md);
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: 0.3px;
        }

        .navbar .btn-link {
            text-decoration: none;
        }

        .navbar .btn-link:hover {
            opacity: 0.92;
        }

        .sidebar {
            background: rgba(255, 255, 255, 0.90);
            backdrop-filter: blur(10px);
            min-height: calc(100vh - 76px);
            box-shadow: var(--shadow-sm);
            border-right: 1px solid var(--border-color);
        }

        .sidebar .nav-link {
            color: #4b5563;
            padding: 0.7rem 1rem;
            margin: 0.15rem 0.75rem;
            border-radius: 12px;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(245, 158, 11, 0.12);
            color: #9a3412;
        }

        .sidebar .nav-link.active {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.95), rgba(180, 83, 9, 0.95));
            color: #111827;
            box-shadow: 0 10px 20px rgba(180, 83, 9, 0.25);
        }

        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }

        .main-content {
            padding: 2rem;
            min-height: calc(100vh - 76px);
        }

        .card {
            border: none;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(124, 45, 18, 0.08);
        }

        .card-header {
            background: rgba(255, 247, 237, 0.75);
            border-bottom: 1px solid rgba(124, 45, 18, 0.10);
        }

        .form-select,
        .form-control {
            border-radius: 12px;
            border-color: rgba(124, 45, 18, 0.18);
        }

        .form-select:focus,
        .form-control:focus,
        .btn:focus {
            box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.25);
            border-color: rgba(245, 158, 11, 0.75);
        }

        .btn-primary {
            background: linear-gradient(135deg, rgba(245, 158, 11, 1), rgba(180, 83, 9, 1));
            border-color: rgba(180, 83, 9, 0.55);
            color: #111827;
            border-radius: 12px;
            font-weight: 700;
        }

        .btn-primary:hover {
            filter: brightness(0.98);
            transform: translateY(-1px);
        }

        .btn-outline-secondary {
            border-radius: 12px;
            border-color: rgba(124, 45, 18, 0.35);
            color: #9a3412;
        }

        .btn-outline-secondary:hover {
            background-color: rgba(245, 158, 11, 0.14);
            border-color: rgba(124, 45, 18, 0.45);
            color: #7c2d12;
        }

        .dropdown-menu {
            border: none;
            box-shadow: var(--shadow-md);
            border-radius: 14px;
            overflow: hidden;
        }

        .dropdown-item:active {
            background-color: rgba(245, 158, 11, 0.18);
            color: #7c2d12;
        }

        .alert {
            border-radius: var(--radius);
            border: none;
        }

        .table th {
            background-color: rgba(255, 247, 237, 0.9);
            border-top: none;
            font-weight: 600;
        }

        .table {
            border-color: rgba(124, 45, 18, 0.10);
        }

        .badge.bg-danger {
            background-color: #ef4444 !important;
        }

        .badge {
            font-size: 0.75rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 76px;
                left: -250px;
                width: 250px;
                z-index: 1000;
                transition: left 0.3s;
            }

            .sidebar.show {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .navbar-toggler {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= site_url('dashboard') ?>">
                <i class="fas fa-boxes"></i> CHAKANOKS
            </a>

            <button class="navbar-toggler d-lg-none" type="button" onclick="toggleSidebar()">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="d-flex align-items-center ms-auto">
                <div class="dropdown me-3">
                    <?php $allRoles = session('user_roles') ?? []; $activeRole = session('user_role'); ?>
                    <?php if (!empty($allRoles)): ?>
                        <form method="post" action="<?= site_url('switch-role') ?>">
                            <?= csrf_field() ?>
                            <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                                <?php foreach ($allRoles as $role): ?>
                                    <option value="<?= esc($role) ?>" <?= $role === $activeRole ? 'selected' : '' ?>>
                                        <?= esc($role) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    <?php endif; ?>
                </div>
                <div class="me-3" id="navbarNotifications" style="cursor:pointer; position:relative;">
                    <span class="text-white">
                        <i class="fas fa-bell"></i>
                    </span>
                    <span id="navbarNotificationsBadge" class="badge bg-danger" style="position:absolute; top:-4px; right:-8px; font-size:0.65rem; display:none;">0</span>
                    <div id="navbarNotificationsPanel" class="card" style="position:absolute; right:0; top:130%; min-width:280px; max-width:340px; display:none; z-index:1050;">
                        <div class="card-header py-2 d-flex justify-content-between align-items-center">
                            <span class="small fw-bold">Notifications</span>
                            <button type="button" class="btn btn-sm btn-link p-0" id="navbarNotificationsClose" style="font-size:0.8rem;">Close</button>
                        </div>
                        <div class="card-body p-0" id="navbarNotificationsList">
                            <div class="p-2 small text-muted">Loading notifications...</div>
                        </div>
                    </div>
                </div>
                <div class="dropdown">
                    <button class="btn btn-link text-white dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> <?= session('user_full_name') ?? 'User' ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="<?= site_url('profile') ?>">
                                <i class="fas fa-user me-2"></i> Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= site_url('reports') ?>">
                                <i class="fas fa-file-alt me-2"></i> Reports
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= site_url('activity-logs') ?>">
                                <i class="fas fa-history me-2"></i> Activity Logs
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= site_url('settings') ?>">
                                <i class="fas fa-cog me-2"></i> Settings &amp; Users
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?= site_url('logout') ?>">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-2 px-0 sidebar d-lg-block" id="sidebar">
                <nav class="nav flex-column py-3">
                    <a class="nav-link" href="<?= site_url('dashboard') ?>">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>

                    <?php if (in_array(session('user_role'), ['branch_manager', 'inventory_staff'])): ?>
                        <a class="nav-link" href="<?= site_url('order') ?>">
                            <i class="fas fa-shopping-cart"></i> Orders
                        </a>
                        <a class="nav-link" href="<?= site_url('inventory') ?>">
                            <i class="fas fa-warehouse"></i> Inventory
                        </a>
                        <a class="nav-link" href="<?= site_url('supply-request/create') ?>">
                            <i class="fas fa-clipboard-list"></i> Supply Requests
                        </a>
                    <?php endif; ?>

                    <?php if (session('user_role') === 'supplier'): ?>
                        <a class="nav-link" href="<?= site_url('purchase-order/supplier') ?>">
                            <i class="fas fa-truck"></i> Purchase Orders
                        </a>
                    <?php endif; ?>

                    <?php if (in_array(session('user_role'), ['central_admin', 'system_admin'])): ?>
                        <a class="nav-link" href="<?= site_url('supply-request') ?>">
                            <i class="fas fa-tasks"></i> Supply Requests
                        </a>
                        <a class="nav-link" href="<?= site_url('purchase-order') ?>">
                            <i class="fas fa-truck"></i> Purchase Orders
                        </a>
                        <a class="nav-link" href="<?= site_url('inventory') ?>">
                            <i class="fas fa-warehouse"></i> Inventory
                        </a>
                        <a class="nav-link" href="<?= site_url('branches') ?>">
                            <i class="fas fa-code-branch"></i> Branches
                        </a>
                        <a class="nav-link" href="<?= site_url('system-admin') ?>">
                            <i class="fas fa-cogs"></i> System Admin
                        </a>
                    <?php endif; ?>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-lg-10 main-content">
                <?= $this->renderSection('content') ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggler = document.querySelector('.navbar-toggler');

            if (!sidebar.contains(event.target) && !toggler.contains(event.target) && window.innerWidth <= 768) {
                sidebar.classList.remove('show');
            }
        });

        // Navbar notifications: load count and handle panel interactions
        document.addEventListener('DOMContentLoaded', function() {
            const wrapper = document.getElementById('navbarNotifications');
            const badge = document.getElementById('navbarNotificationsBadge');
            const panel = document.getElementById('navbarNotificationsPanel');
            const listEl = document.getElementById('navbarNotificationsList');
            const closeBtn = document.getElementById('navbarNotificationsClose');

            if (!wrapper || !badge || !panel || !listEl) return;

            function updateBadge(unreadCount) {
                if (unreadCount > 0) {
                    badge.textContent = unreadCount;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }

            function renderNotifications(data) {
                const notifications = data.notifications || [];
                const unreadCount = data.unreadCount || 0;
                updateBadge(unreadCount);

                if (notifications.length === 0) {
                    listEl.innerHTML = '<div class="p-2 small text-muted">No notifications at this time.</div>';
                    return;
                }

                let html = '<ul class="list-group list-group-flush">';
                notifications.forEach(n => {
                    const created = n.created_at ? new Date(n.created_at).toLocaleString() : '';
                    const isRead = n.is_read === 1 || n.is_read === '1';
                    html += '<li class="list-group-item p-2" data-id="' + n.id + '" style="cursor:pointer;">'
                         +  '<div class="small fw-bold' + (isRead ? ' text-muted' : '') + '">' + (n.title ?? 'Notification') + '</div>'
                         +  '<div class="small text-muted">' + (n.message ?? '') + '</div>'
                         +  '<div class="small text-muted mt-1">' + created + '</div>'
                         +  '</li>';
                });
                html += '</ul>';
                listEl.innerHTML = html;
            }

            function loadNotifications() {
                listEl.innerHTML = '<div class="p-2 small text-muted">Loading notifications...</div>';
                fetch('<?= site_url('api/notifications') ?>?unreadOnly=0&limit=10', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(renderNotifications)
                .catch(() => {
                    listEl.innerHTML = '<div class="p-2 small text-muted">Unable to load notifications.</div>';
                });
            }

            // Initial badge load
            fetch('<?= site_url('api/notifications') ?>?unreadOnly=1&limit=1', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                updateBadge(data.unreadCount || 0);
            })
            .catch(() => {});

            // Toggle panel on bell click
            wrapper.addEventListener('click', function(event) {
                event.stopPropagation();
                const isVisible = panel.style.display === 'block';
                if (isVisible) {
                    panel.style.display = 'none';
                } else {
                    panel.style.display = 'block';
                    loadNotifications();
                }
            });

            if (closeBtn) {
                closeBtn.addEventListener('click', function(event) {
                    event.stopPropagation();
                    panel.style.display = 'none';
                });
            }

            // Click on notification: mark as read
            listEl.addEventListener('click', function(event) {
                const li = event.target.closest('li[data-id]');
                if (!li) return;
                const id = li.getAttribute('data-id');
                if (!id) return;

                fetch('<?= site_url('api/notifications') ?>/' + id + '/read', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(() => {
                    // Reload list and badge after marking read
                    loadNotifications();
                })
                .catch(() => {
                    // Ignore errors for now
                });
            });

            // Close panel when clicking outside
            document.addEventListener('click', function(event) {
                if (!wrapper.contains(event.target)) {
                    panel.style.display = 'none';
                }
            });
        });
    </script>

    <?= $this->renderSection('scripts') ?>
</body>
</html>
