<?php
$title = 'Manager Dashboard';
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
            background-color: #16a085;
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
            color: #16a085;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }
        
        h3 {
            color: #16a085;
            margin: 1.5rem 0 1rem 0;
            font-size: 1.3rem;
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
            border-left: 4px solid #16a085;
        }
        
        .stat-card h3 {
            color: #7f8c8d;
            font-size: 0.9rem;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
            color: inherit;
        }
        
        .stat-card .value {
            font-size: 2rem;
            font-weight: bold;
            color: #16a085;
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
            margin-top: 1rem;
        }
        
        table th {
            background-color: #16a085;
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
        
        .branch-info {
            background-color: #ecf0f1;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
        }
        
        .branch-info strong {
            color: #16a085;
        }
        
        .nav-links {
            display: flex;
            gap: 1rem;
        }

        .action-card {
            background: linear-gradient(135deg, #16a085 0%, #1abc9c 100%);
            color: white;
            padding: 2rem;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            display: block;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: none;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .action-card h4 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }

        .action-card p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .action-card .badge {
            display: inline-block;
            background-color: rgba(255,255,255,0.3);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-top: 1rem;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        /* Alert Styles */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: none;
            animation: slideDown 0.3s ease-out;
        }

        .alert.success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            display: block;
        }

        .alert.error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            display: block;
        }

        .alert.info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            display: block;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s ease-out;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .modal-header h2 {
            color: #16a085;
            font-size: 1.5rem;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
            transition: color 0.2s;
        }

        .close-btn:hover {
            color: #333;
        }

        .modal-body {
            margin-bottom: 1.5rem;
            max-height: 400px;
            overflow-y: auto;
        }

        .modal-footer {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            padding-top: 1rem;
            border-top: 2px solid #f0f0f0;
        }

        .btn {
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: #16a085;
            color: white;
        }

        .btn-primary:hover {
            background-color: #128a6f;
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #7f8c8d;
        }

        .btn-success {
            background-color: #27ae60;
            color: white;
        }

        .btn-success:hover {
            background-color: #229954;
        }

        .stats-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin: 1rem 0;
        }

        .stat-info-item {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 6px;
            border-left: 4px solid #16a085;
        }

        .stat-info-item strong {
            color: #16a085;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #16a085;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1><?= $title ?></h1>
        <div class="nav-links">
            <a href="<?= site_url('order') ?>">Orders</a>
            <a href="<?= site_url('inventory') ?>">Inventory</a>
            <a href="<?= site_url('logout') ?>">Logout</a>
        </div>
    </nav>

    <div class="container">
        <?php if (!empty($branchData['branch'])): ?>
            <h2>Welcome back, <?= session('user_full_name') ?? 'Manager' ?>!</h2>
            
            <div class="branch-info">
                <p><strong>Branch:</strong> <?= esc($branchData['branch']->name) ?> (<?= esc($branchData['branch']->code) ?>)</p>
                <?php if (!empty($branchData['branch']->city)): ?>
                    <p><strong>City:</strong> <?= esc($branchData['branch']->city) ?></p>
                <?php endif; ?>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Stock Items</h3>
                    <div class="value"><?= $branchData['itemCount'] ?? 0 ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Units in Stock</h3>
                    <div class="value"><?= $branchData['totalStock'] ?? 0 ?></div>
                </div>
            </div>

            <!-- Alert Messages -->
            <div id="alertContainer"></div>

            <!-- Order Management Section -->
            <div class="action-grid">
                <button class="action-card" onclick="handleActionClick('manage');" type="button">
                    <h4>📦 Manage Orders</h4>
                    <p>View and manage your orders</p>
                    <div class="badge">
                        <?= ($branchData['draftOrders'] ?? 0) + ($branchData['pendingOrders'] ?? 0) + ($branchData['approvedOrders'] ?? 0) ?> Total
                    </div>
                </button>
                <button class="action-card" onclick="handleActionClick('create');" type="button">
                    <h4>➕ Create New Order</h4>
                    <p>Create a new purchase order</p>
                    <div class="badge">
                        <?= ($branchData['draftOrders'] ?? 0) ?> Draft
                    </div>
                </button>
                <div class="action-card" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); cursor: default;">
                    <h4>⏳ Pending Approval</h4>
                    <p>Your orders awaiting admin approval</p>
                    <div class="badge">
                        <?= ($branchData['pendingOrders'] ?? 0) ?> Pending
                    </div>
                </div>
            </div>

            <!-- Low Stock Items Section -->
            <div class="section">
                <h3>Low Stock Items (Alert)</h3>
                <?php if (!empty($branchData['lowStockItems'])): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Current Stock</th>
                                <th>Minimum Stock</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($branchData['lowStockItems'] as $item): ?>
                                <tr>
                                    <td><?= esc($item['name']) ?></td>
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
                <?php if (!empty($branchData['recentActivity'])): ?>
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
                            <?php foreach ($branchData['recentActivity'] as $log): ?>
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
                        <p>No activity logged yet in your branch.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="empty-message">
                <h2>No Branch Assigned</h2>
                <p>Your account does not have a branch assigned. Please contact an administrator.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="confirmTitle">Confirm Action</h2>
                <button class="close-btn" onclick="closeModal('confirmModal')">&times;</button>
            </div>
            <div class="modal-body" id="confirmBody">
                Are you sure you want to continue?
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('confirmModal')">Cancel</button>
                <button class="btn btn-primary" onclick="executeAction()">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>✓ Success</h2>
                <button class="close-btn" onclick="closeModal('successModal')">&times;</button>
            </div>
            <div class="modal-body" id="successMessage">
                Action completed successfully!
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" onclick="handleSuccessClose()">Close</button>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 style="color: #dc3545;">✗ Error</h2>
                <button class="close-btn" onclick="closeModal('errorModal')">&times;</button>
            </div>
            <div class="modal-body" id="errorMessage">
                An error occurred. Please try again.
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('errorModal')">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Global variables for action tracking
        let pendingAction = null;
        let actionData = {};

        // Show Alert Message
        function showAlert(message, type = 'success') {
            const alertContainer = document.getElementById('alertContainer');
            const alertId = 'alert-' + Date.now();
            const alertHtml = `
                <div id="${alertId}" class="alert ${type}">
                    <strong>${type.charAt(0).toUpperCase() + type.slice(1)}:</strong> ${message}
                    <button onclick="this.parentElement.style.display='none';" style="float: right; background: none; border: none; cursor: pointer; font-size: 1.2rem;">×</button>
                </div>
            `;
            alertContainer.innerHTML += alertHtml;
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                const el = document.getElementById(alertId);
                if (el) el.style.display = 'none';
            }, 5000);
        }

        // Modal Management Functions
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('show');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        // Handle Main Action Click
        function handleActionClick(actionType) {
            switch(actionType) {
                case 'manage':
                    showActionModal('Manage Orders', 
                        'You will be redirected to view all your orders. Continue?',
                        () => navigateToPage('<?= site_url('order') ?>'));
                    break;
                case 'create':
                    showActionModal('Create New Order',
                        'Start creating a new supply order? You can save it as draft.',
                        () => navigateToPage('<?= site_url('order/create') ?>'));
                    break;
                case 'pending':
                    showActionModal('View Pending Orders',
                        'View all orders awaiting admin approval?',
                        () => navigateToPage('<?= site_url('order/pending') ?>'));
                    break;
            }
        }

        // Show Confirmation Modal
        function showActionModal(title, message, callback) {
            document.getElementById('confirmTitle').textContent = title;
            document.getElementById('confirmBody').textContent = message;
            pendingAction = callback;
            openModal('confirmModal');
        }

        // Execute Pending Action
        function executeAction() {
            closeModal('confirmModal');
            if (pendingAction && typeof pendingAction === 'function') {
                try {
                    pendingAction();
                } catch (error) {
                    showErrorModal('Failed to execute action', error.message);
                }
            }
        }

        // Navigate to Page with Success Message
        function navigateToPage(url) {
            // Show loading state
            const btn = event.target.closest('.btn-primary');
            if (btn) {
                const originalText = btn.innerHTML;
                btn.innerHTML = '<span class="loading"></span> Loading...';
                btn.disabled = true;
            }

            // Simulate processing
            setTimeout(() => {
                try {
                    window.location.href = url;
                } catch (error) {
                    showErrorModal('Navigation Error', 'Could not navigate to page');
                }
            }, 500);
        }

        // Show Success Modal
        function showSuccessModal(title, message, callback = null) {
            document.getElementById('successMessage').textContent = message;
            actionData.successCallback = callback;
            openModal('successModal');
        }

        // Handle Success Modal Close
        function handleSuccessClose() {
            closeModal('successModal');
            if (actionData.successCallback && typeof actionData.successCallback === 'function') {
                actionData.successCallback();
            }
        }

        // Show Error Modal
        function showErrorModal(title, message) {
            document.getElementById('errorMessage').innerHTML = `<strong>${title}:</strong> ${message}`;
            openModal('errorModal');
        }

        // Legacy navigation function (fallback)
        function navigateTo(url) {
            window.location.href = url;
        }

        // Close modals when clicking outside
        document.addEventListener('click', function(event) {
            const confirmModal = document.getElementById('confirmModal');
            const successModal = document.getElementById('successModal');
            const errorModal = document.getElementById('errorModal');
            
            if (event.target === confirmModal) closeModal('confirmModal');
            if (event.target === successModal) closeModal('successModal');
            if (event.target === errorModal) closeModal('errorModal');
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal('confirmModal');
                closeModal('successModal');
                closeModal('errorModal');
            }
        });

        // Initialization
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Manager Dashboard initialized successfully');
        });
    </script>
</body>
</html>
