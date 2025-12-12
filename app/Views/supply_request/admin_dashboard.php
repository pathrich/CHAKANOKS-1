<?php
// app/Views/supply_request/admin_dashboard.php
?>

<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1>Supply Request Management</h1>
            <p class="text-muted">Review and approve supply requests from branches</p>
        </div>
    </div>

    <!-- Tabs for filtering requests -->
    <div class="row mt-4">
        <div class="col-12">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="pending-tab" data-bs-toggle="tab" href="#pending" role="tab">
                        Pending <span class="badge bg-warning"><?= count($pendingRequests ?? []) ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="all-tab" data-bs-toggle="tab" href="#all" role="tab">
                        All Requests
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="tab-content mt-3">
        <!-- Pending Requests Tab -->
        <div class="tab-pane fade show active" id="pending" role="tabpanel">
            <?php if (!empty($pendingRequests)): ?>
                <div class="row">
                    <?php foreach ($pendingRequests as $request): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card border-warning">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            Request #<?= $request['id'] ?>
                                            <span class="badge bg-warning">Pending</span>
                                        </h5>
                                        <small class="text-muted"><?= date('M d, Y', strtotime($request['created_at'])) ?></small>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="mb-2">
                                        <strong>Branch:</strong> <?= $request['branch_name'] ?>
                                    </p>
                                    <p class="mb-2">
                                        <strong>Requested by:</strong> <?= $request['requester_name'] ?>
                                    </p>
                                    <p class="mb-2">
                                        <strong>Total Items:</strong> <?= $request['total_items'] ?>
                                    </p>
                                    <?php if ($request['notes']): ?>
                                        <p class="mb-2">
                                            <strong>Notes:</strong> <?= htmlspecialchars($request['notes']) ?>
                                        </p>
                                    <?php endif; ?>

                                    <!-- Items List -->
                                    <h6 class="mt-3 mb-2">Items Requested:</h6>
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>SKU</th>
                                                <th class="text-end">Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($request['items'] as $item): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                                    <td><?= htmlspecialchars($item['sku']) ?></td>
                                                    <td class="text-end"><?= $item['quantity_requested'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-footer d-flex gap-2">
                                    <button class="btn btn-sm btn-success approve-btn" 
                                            data-request-id="<?= $request['id'] ?>">
                                        Approve
                                    </button>
                                    <button class="btn btn-sm btn-danger reject-btn" 
                                            data-request-id="<?= $request['id'] ?>">
                                        Reject
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    No pending supply requests at this time.
                </div>
            <?php endif; ?>
        </div>

        <!-- All Requests Tab -->
        <div class="tab-pane fade" id="all" role="tabpanel">
            <div id="all-requests-container">
                <p class="text-muted">Loading...</p>
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve Supply Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to approve this supply request?</p>
                <div class="form-group">
                    <label for="approvalNotes">Notes (optional):</label>
                    <textarea class="form-control" id="approvalNotes" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmApproveBtn">Approve</button>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Supply Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Please provide a reason for rejecting this request:</p>
                <div class="form-group">
                    <label for="rejectionReason">Reason for Rejection:</label>
                    <textarea class="form-control" id="rejectionReason" rows="4" required></textarea>
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
let currentRequestId = null;

// Approve button handler
document.querySelectorAll('.approve-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        currentRequestId = this.dataset.requestId;
        document.getElementById('approvalNotes').value = '';
        const approveModal = new bootstrap.Modal(document.getElementById('approveModal'));
        approveModal.show();
    });
});

// Confirm approve
document.getElementById('confirmApproveBtn').addEventListener('click', function() {
    const notes = document.getElementById('approvalNotes').value;
    
    fetch('<?= site_url('supply-request/approve') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            request_id: currentRequestId,
            approval_notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Supply request approved! Branch manager has been notified.');
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to approve request'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while approving the request');
    });

    bootstrap.Modal.getInstance(document.getElementById('approveModal')).hide();
});

// Reject button handler
document.querySelectorAll('.reject-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        currentRequestId = this.dataset.requestId;
        document.getElementById('rejectionReason').value = '';
        const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
        rejectModal.show();
    });
});

// Confirm reject
document.getElementById('confirmRejectBtn').addEventListener('click', function() {
    const reason = document.getElementById('rejectionReason').value;
    
    if (!reason.trim()) {
        alert('Please provide a reason for rejection');
        return;
    }
    
    fetch('<?= site_url('supply-request/reject') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            request_id: currentRequestId,
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Supply request rejected.');
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to reject request'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while rejecting the request');
    });

    bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
});

// All Requests tab loader
let allRequestsLoaded = false;
async function loadAllRequests() {
    const container = document.getElementById('all-requests-container');
    container.innerHTML = '<p class="text-muted">Loading...</p>';

    try {
        const res = await fetch('<?= site_url('supply-request/all') ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (!data.success) {
            container.innerHTML = `<div class="alert alert-danger">${data.error || 'Failed to load requests'}</div>`;
            return;
        }

        const requests = data.requests || [];
        if (requests.length === 0) {
            container.innerHTML = '<div class="alert alert-info">No supply requests found.</div>';
            return;
        }

        let html = '<div class="table-responsive">';
        html += '<table class="table table-hover align-middle">';
        html += '<thead class="table-light"><tr>';
        html += '<th>Request #</th><th>Branch</th><th>Requested By</th><th>Status</th><th>Items</th><th>Created</th>';
        html += '</tr></thead><tbody>';

        requests.forEach(r => {
            const created = r.created_at ? new Date(r.created_at).toLocaleString() : '';
            const itemCount = (r.items || []).length;
            html += `<tr>`;
            html += `<td><strong>#${r.id}</strong></td>`;
            html += `<td>${escapeHtml(r.branch_name || '')}</td>`;
            html += `<td>${escapeHtml(r.requester_name || '')}</td>`;
            html += `<td><span class="badge bg-${statusColor(r.status)}">${escapeHtml(r.status || '')}</span></td>`;
            html += `<td>${itemCount}</td>`;
            html += `<td>${created}</td>`;
            html += `</tr>`;
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;
        allRequestsLoaded = true;
    } catch (e) {
        console.error(e);
        container.innerHTML = '<div class="alert alert-danger">Failed to load requests (network/server error).</div>';
    }
}

function statusColor(status) {
    const map = { Pending: 'warning', Approved: 'success', Rejected: 'danger', Fulfilled: 'info' };
    return map[status] || 'secondary';
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

document.getElementById('all-tab').addEventListener('shown.bs.tab', function() {
    if (!allRequestsLoaded) {
        loadAllRequests();
    }
});
</script>

<?= $this->endSection() ?>
