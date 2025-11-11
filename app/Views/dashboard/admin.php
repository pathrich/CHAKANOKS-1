<?php
$title = 'Admin Dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }
        
        .navbar {
            background-color: #2c3e50;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar h1 {
            font-size: 1.5rem;
        }
        
        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 2rem;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            background-color: #e74c3c;
            transition: background-color 0.3s;
        }
        
        .navbar a:hover {
            background-color: #c0392b;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        h2 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #3498db;
        }
        
        .stat-card h3 {
            color: #7f8c8d;
            font-size: 0.9rem;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }
        
        .stat-card .value {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .section {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th {
            background-color: #34495e;
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }
        
        table td {
            padding: 1rem;
            border-bottom: 1px solid #ecf0f1;
        }
        
        table tr:hover {
            background-color: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .badge-warning {
            background-color: #f39c12;
            color: white;
        }
        
        .badge-danger {
            background-color: #e74c3c;
            color: white;
        }
        
        .empty-message {
            text-align: center;
            color: #95a5a6;
            padding: 2rem;
        }
        
        .nav-links {
            display: flex;
            gap: 1rem;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1><?= $title ?></h1>
        <div class="nav-links">
            <a href="<?= site_url('inventory') ?>">Inventory</a>
            <a href="<?= site_url('logout') ?>">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h2>Welcome back, <?= session('user_full_name') ?? 'Admin' ?>!</h2>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Branches</h3>
                <div class="value"><?= $totalBranches ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="value"><?= $totalUsers ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Items</h3>
                <div class="value"><?= $totalItems ?></div>
            </div>
            <div class="stat-card">
                <h3>Categories</h3>
                <div class="value"><?= $totalCategories ?></div>
            </div>
        </div>

        <!-- Low Stock Items Section -->
        <div class="section">
            <h3>Low Stock Items (Alert)</h3>
            <?php if (!empty($lowStockItems)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Branch</th>
                            <th>Current Stock</th>
                            <th>Minimum Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lowStockItems as $item): ?>
                            <tr>
                                <td><?= esc($item['name']) ?></td>
                                <td><?= esc($item['branch_name']) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td><?= $item['min_stock'] ?></td>
                                <td>
                                    <?php if ($item['quantity'] == 0): ?>
                                        <span class="badge badge-danger">OUT OF STOCK</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">LOW STOCK</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-message">
                    <p>No low stock items at this time. All items are well stocked.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Activity Section -->
        <div class="section">
            <h3>Recent Activity</h3>
            <?php if (!empty($activityLogs)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>Date/Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activityLogs as $log): ?>
                            <tr>
                                <td><?= esc($log['full_name'] ?? 'N/A') ?></td>
                                <td><?= esc($log['action']) ?></td>
                                <td><?= esc($log['details'] ?? '-') ?></td>
                                <td><?= $log['created_at'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-message">
                    <p>No activity logged yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
