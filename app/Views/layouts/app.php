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

    <style>
        :root {
            --primary-color: #16a085;
            --secondary-color: #2c3e50;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --info-color: #3498db;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.25rem;
        }

        .sidebar {
            background: white;
            min-height: calc(100vh - 76px);
            box-shadow: 2px 0 4px rgba(0,0,0,0.1);
        }

        .sidebar .nav-link {
            color: var(--dark-color);
            padding: 0.75rem 1.5rem;
            border-radius: 0;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover {
            background-color: var(--light-color);
            color: var(--primary-color);
        }

        .sidebar .nav-link.active {
            background-color: var(--primary-color);
            color: white;
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
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #138f7a;
            border-color: #138f7a;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .alert {
            border-radius: 8px;
            border: none;
        }

        .table th {
            background-color: var(--light-color);
            border-top: none;
            font-weight: 600;
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
                <div class="dropdown">
                    <button class="btn btn-link text-white dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> <?= session('user_full_name') ?? 'User' ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= site_url('dashboard') ?>"><i class="fas fa-user-edit"></i> Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= site_url('logout') ?>"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
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

                    <?php if (session('user_role') === 'logistics_coordinator'): ?>
                        <a class="nav-link" href="<?= site_url('deliveries') ?>">
                            <i class="fas fa-shipping-fast"></i> Deliveries
                        </a>
                    <?php endif; ?>

                    <?php if (session('user_role') === 'supplier'): ?>
                        <a class="nav-link" href="<?= site_url('purchase-order/supplier') ?>">
                            <i class="fas fa-truck"></i> Purchase Orders
                        </a>
                    <?php endif; ?>

                    <?php if (in_array(session('user_role'), ['central_admin', 'system_admin'])): ?>
                        <a class="nav-link" href="<?= site_url('order/pending') ?>">
                            <i class="fas fa-clock"></i> Pending Orders
                        </a>
                        <a class="nav-link" href="<?= site_url('supply-request') ?>">
                            <i class="fas fa-tasks"></i> Supply Requests
                        </a>
                        <a class="nav-link" href="<?= site_url('purchase-order') ?>">
                            <i class="fas fa-truck"></i> Purchase Orders
                        </a>
                        <a class="nav-link" href="<?= site_url('inventory') ?>">
                            <i class="fas fa-warehouse"></i> Inventory
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
    </script>

    <?= $this->renderSection('scripts') ?>
</body>
</html>
