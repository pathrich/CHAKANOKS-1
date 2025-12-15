<?php
// app/Views/order/pending.php
?>

<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1>Pending Orders</h1>
                    <p class="text-muted">Review and approve orders from branch managers</p>
                </div>
                <a href="<?= site_url('dashboard') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Orders List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Orders Awaiting Approval</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($orders)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order #</th>
                                        <th>Branch</th>
                                        <th>Created By</th>
                                        <th>Created</th>
                                        <th>Items</th>
                                        <th>Total Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>
                                                <strong><?= $order['order_number'] ?></strong>
                                            </td>
                                            <td><?= esc($order['branch_name']) ?></td>
                                            <td><?= esc($order['created_by_name']) ?></td>
                                            <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                            <td><?= $order['total_items'] ?></td>
                                            <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info view-order-btn" data-order-id="<?= $order['id'] ?>" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-success approve-order-btn" data-order-id="<?= $order['id'] ?>" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger reject-order-btn" data-order-id="<?= $order['id'] ?>" title="Reject">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No pending orders at this time.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Order Modal -->
<div class="modal fade" id="viewOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetails">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Approve Order Modal -->
<div class="modal fade" id="approveOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to approve this order?</p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmApproveBtn">Approve</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Order Modal -->
<div class="modal fade" id="rejectOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reject this order?</p>
                <div class="mb-3">
                    <label for="rejectReason" class="form-label">Reason for rejection:</label>
                    <textarea class="form-control" id="rejectReason" rows="3" placeholder="Please provide a reason..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRejectBtn">Reject</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentOrderId = null;

// View Order Details
document.querySelectorAll('.view-order-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        currentOrderId = this.dataset.orderId;
        // For now, just show a placeholder. In a real implementation, you'd fetch order details
        const orderDetails = document.getElementById('orderDetails');
        orderDetails.innerHTML = '<p>Loading order details...</p>';
        const viewModal = new bootstrap.Modal(document.getElementById('viewOrderModal'));
        viewModal.show();
    });
});

// Approve Order
document.querySelectorAll('.approve-order-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        currentOrderId = this.dataset.orderId;
        const approveModal = new bootstrap.Modal(document.getElementById('approveOrderModal'));
        approveModal.show();
    });
});

document.getElementById('confirmApproveBtn').addEventListener('click', function() {
    fetch('<?= site_url('order/approve') ?>', {
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
            alert('Order approved successfully!');
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to approve order'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });

    bootstrap.Modal.getInstance(document.getElementById('approveOrderModal')).hide();
});

// Reject Order
document.querySelectorAll('.reject-order-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        currentOrderId = this.dataset.orderId;
        const rejectModal = new bootstrap.Modal(document.getElementById('rejectOrderModal'));
        rejectModal.show();
    });
});

document.getElementById('confirmRejectBtn').addEventListener('click', function() {
    const reason = document.getElementById('rejectReason').value.trim();
    if (!reason) {
        alert('Please provide a reason for rejection');
        return;
    }

    fetch('<?= site_url('order/cancel') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ order_id: currentOrderId, reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Order rejected successfully!');
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to reject order'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });

    bootstrap.Modal.getInstance(document.getElementById('rejectOrderModal')).hide();
});
</script>

<?= $this->endSection() ?>
