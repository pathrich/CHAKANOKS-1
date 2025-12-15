<?php
// app/Views/order/manager_dashboard.php
?>

<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1>Orders Management</h1>
                    <p class="text-muted">Create and manage orders for your branch</p>
                </div>
                <a href="<?= site_url('order/create') ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Order
                </a>
            </div>
        </div>
    </div>

    <!-- Orders List -->
    <div class="row mt-4">
        <div class="col-12">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="draft-tab" data-bs-toggle="tab" href="#draft" role="tab">
                        Draft
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pending-tab" data-bs-toggle="tab" href="#pending" role="tab">
                        Pending
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="approved-tab" data-bs-toggle="tab" href="#approved" role="tab">
                        Approved
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="all-tab" data-bs-toggle="tab" href="#all" role="tab">
                        All Orders
                    </a>
                </li>
            </ul>

            <div class="tab-content mt-3">
                <!-- Draft Orders -->
                <div class="tab-pane fade show active" id="draft" role="tabpanel">
                    <?php 
                        $draftOrders = array_filter($orders, fn($o) => $o['status'] === 'Draft');
                    ?>
                    <?php if (!empty($draftOrders)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order #</th>
                                        <th>Created</th>
                                        <th>Items</th>
                                        <th>Total Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($draftOrders as $order): ?>
                                        <tr>
                                            <td>
                                                <strong><?= $order['order_number'] ?></strong>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                            <td><?= $order['total_items'] ?></td>
                                            <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                                            <td>
                                                <a href="<?= site_url('order/edit/' . $order['id']) ?>" class="btn btn-sm btn-info" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-success submit-order-btn" data-order-id="<?= $order['id'] ?>" title="Submit for Approval">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger delete-order-btn" data-order-id="<?= $order['id'] ?>" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            No draft orders. <a href="<?= site_url('order/create') ?>">Create one now</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pending Orders -->
                <div class="tab-pane fade" id="pending" role="tabpanel">
                    <?php 
                        $pendingOrders = array_filter($orders, fn($o) => $o['status'] === 'Pending');
                    ?>
                    <?php if (!empty($pendingOrders)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order #</th>
                                        <th>Created</th>
                                        <th>Items</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingOrders as $order): ?>
                                        <tr>
                                            <td><strong><?= $order['order_number'] ?></strong></td>
                                            <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                            <td><?= $order['total_items'] ?></td>
                                            <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                                            <td><span class="badge bg-warning">Pending Approval</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No pending orders</div>
                    <?php endif; ?>
                </div>

                <!-- Approved Orders -->
                <div class="tab-pane fade" id="approved" role="tabpanel">
                    <?php 
                        $approvedOrders = array_filter($orders, fn($o) => $o['status'] === 'Approved');
                    ?>
                    <?php if (!empty($approvedOrders)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order #</th>
                                        <th>Created</th>
                                        <th>Items</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($approvedOrders as $order): ?>
                                        <tr>
                                            <td><strong><?= $order['order_number'] ?></strong></td>
                                            <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                            <td><?= $order['total_items'] ?></td>
                                            <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                                            <td><span class="badge bg-success">Approved</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No approved orders</div>
                    <?php endif; ?>
                </div>

                <!-- All Orders -->
                <div class="tab-pane fade" id="all" role="tabpanel">
                    <?php if (!empty($orders)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order #</th>
                                        <th>Created</th>
                                        <th>Items</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><strong><?= $order['order_number'] ?></strong></td>
                                            <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                            <td><?= $order['total_items'] ?></td>
                                            <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                                            <td>
                                                <?php 
                                                    $badgeColor = match($order['status']) {
                                                        'Draft' => 'secondary',
                                                        'Pending' => 'warning',
                                                        'Approved' => 'success',
                                                        'Shipped' => 'info',
                                                        'Delivered' => 'success',
                                                        'Cancelled' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                ?>
                                                <span class="badge bg-<?= $badgeColor ?>"><?= $order['status'] ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No orders found</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Submit Order Modal -->
<div class="modal fade" id="submitOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit Order for Approval</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to submit this order for approval?</p>
                <p class="text-muted">Once submitted, you won't be able to edit the order.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmSubmitBtn">Submit</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Order Modal -->
<div class="modal fade" id="deleteOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this draft order?</p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentOrderId = null;

// Submit Order
document.querySelectorAll('.submit-order-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        currentOrderId = this.dataset.orderId;
        const submitModal = new bootstrap.Modal(document.getElementById('submitOrderModal'));
        submitModal.show();
    });
});

document.getElementById('confirmSubmitBtn').addEventListener('click', function() {
    fetch('<?= site_url('order/submit') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ order_id: currentOrderId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Order submitted for approval!');
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to submit order'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });

    bootstrap.Modal.getInstance(document.getElementById('submitOrderModal')).hide();
});

// Delete Order
document.querySelectorAll('.delete-order-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        currentOrderId = this.dataset.orderId;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteOrderModal'));
        deleteModal.show();
    });
});

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    fetch('<?= site_url('order/cancel') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ order_id: currentOrderId, reason: 'User deleted draft order' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Order deleted!');
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to delete order'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });

    bootstrap.Modal.getInstance(document.getElementById('deleteOrderModal')).hide();
});
</script>

<?= $this->endSection() ?>
