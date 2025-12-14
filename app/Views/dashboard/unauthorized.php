<?php
$title = 'Access Denied';
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }
        
        .container {
            background: white;
            padding: 3rem;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 600px;
            text-align: center;
        }
        
        .error-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        h1 {
            color: #e74c3c;
            margin-bottom: 1rem;
        }
        
        p {
            color: #555;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
            padding: 1rem;
            margin: 1.5rem 0;
            text-align: left;
            border-radius: 4px;
        }
        
        .info-box strong {
            color: #2c3e50;
        }
        
        .roles-list {
            text-align: left;
            margin: 1rem 0;
            padding-left: 2rem;
        }
        
        .roles-list li {
            margin: 0.5rem 0;
            color: #555;
        }
        
        .nav-links {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        
        .nav-links a {
            padding: 0.75rem 1.5rem;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .nav-links a:hover {
            background-color: #2980b9;
        }
        
        .nav-links a.logout {
            background-color: #e74c3c;
        }
        
        .nav-links a.logout:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-icon">🔒</div>
        <h1><?= $title ?></h1>
        
        <p>You do not have permission to access the dashboard. Please contact an administrator to grant you the necessary permissions.</p>
        
        <div class="info-box">
            <strong>Your Current Roles:</strong>
            <?php if (!empty($userRoles)): ?>
                <ul class="roles-list">
                    <?php foreach ($userRoles as $role): ?>
                        <li><?= esc($role) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <ul class="roles-list">
                    <li>No roles assigned</li>
                </ul>
            <?php endif; ?>
        </div>
        
        <p><strong>Required Roles:</strong> central_admin, branch_manager</p>
        
        <div class="nav-links">
            <?php
                $roles = $userRoles ?? [];

                // Determine best "home" route based on current role
                if (in_array('inventory_staff', $roles, true) || in_array('branch_manager', $roles, true) || in_array('central_admin', $roles, true)) {
                    $primaryUrl  = site_url('inventory');
                    $primaryText = 'Go to Inventory';
                } elseif (in_array('logistics_coordinator', $roles, true)) {
                    $primaryUrl  = site_url('deliveries');
                    $primaryText = 'Go to Deliveries';
                } elseif (in_array('supplier', $roles, true) || in_array('franchise', $roles, true)) {
                    $primaryUrl  = site_url('purchase-order');
                    $primaryText = 'Go to Purchase Orders';
                } else {
                    $primaryUrl  = site_url('dashboard');
                    $primaryText = 'Go to Dashboard';
                }
            ?>

            <a href="<?= $primaryUrl ?>"><?= esc($primaryText) ?></a>
            <a href="<?= site_url('logout') ?>" class="logout">Logout</a>
        </div>
    </div>
</body>
</html>
