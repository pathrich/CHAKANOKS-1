<?php
$title = 'Supplier Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background: linear-gradient(135deg, #27ae60, #2ecc71); box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .navbar-brand, .nav-link { color: white !important; }
        .card { border: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 8px; margin-bottom: 1.5rem; }
        .card-header { background: linear-gradient(135deg, #27ae60, #2ecc71); color: white; border: 0; }
        .section-title { color: #27ae60; font-weight: 600; margin-bottom: 1.5rem; }
        .po-card { padding: 1.5rem; border-left: 4px solid #27ae60; }
        .status-badge { font-size: 0.85rem; font-weight: 600; padding: 0.4rem 0.8rem; border-radius: 4px; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-confirmed { background-color: #d4edda; color: #155724; }
        .status-shipped { background-color: #d1ecf1; color: #0c5460; }
        .btn-action { margin: 0.25rem; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">Supplier Portal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="<?= site_url('logout') ?>">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        <h1 class="mb-4">Welcome to Your Supplier Portal</h1>

        <!-- Statistics Cards -->
        <div class="row mb-5">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-muted">POs Awaiting Response</h5>
                        <h2 class="text-warning" id="pendingCount">0</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Confirmed POs</h5>
                        <h2 class="text-success" id="confirmedCount">0</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Shipped</h5>
                        <h2 class="text-info" id="shippedCount">0</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Declined</h5>
                        <h2 class="text-danger" id="declinedCount">0</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchase Orders Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Your Purchase Orders</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="poTable">
                        <thead class="table-light">
                            <tr>
                                <th>PO Number</th>
                                <th>Items</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Accept/Confirm Modal -->
    <div class="modal fade" id="acceptModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Confirm Purchase Order</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="acceptForm">
                        <input type="hidden" id="acceptPoId" />
                        <div class="mb-3">
                            <label class="form-label">Notes (optional)</label>
                            <textarea class="form-control" id="acceptNotes" rows="3" placeholder="Delivery date, special notes, etc."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="submitAccept()">Confirm PO</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Changes Modal -->
    <div class="modal fade" id="changesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Request Changes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="changesForm">
                        <input type="hidden" id="changesPoId" />
                        <div class="mb-3">
                            <label class="form-label">Reason for Changes <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="changesReason" rows="4" required placeholder="Please explain what changes are needed"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" onclick="submitChanges()">Request Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Decline Modal -->
    <div class="modal fade" id="declineModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Decline Purchase Order</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="declineForm">
                        <input type="hidden" id="declinePoId" />
                        <div class="mb-3">
                            <label class="form-label">Reason for Decline <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="declineReason" rows="4" required placeholder="Why are you declining this order?"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="submitDecline()">Decline PO</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Ship Modal -->
    <div class="modal fade" id="shipModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Mark as Shipped</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="shipForm">
                        <input type="hidden" id="shipPoId" />
                        <div class="mb-3">
                            <label class="form-label">Tracking Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="trackingNumber" required placeholder="e.g., TRK123456789">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-info" onclick="submitShip()">Mark as Shipped</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentPOs = [];

        function statusMeta(status) {
            const text = (status || '').replace(/_/g, ' ');
            let klass = 'status-pending';
            if (status === 'SUPPLIER_CONFIRMED') klass = 'status-confirmed';
            if (status === 'SHIPPED') klass = 'status-shipped';
            return { text, klass };
        }

        async function loadPOs() {
            const tbody = document.querySelector('#poTable tbody');
            tbody.innerHTML = '<tr><td colspan="6" class="text-muted">Loading...</td></tr>';

            const res = await fetch('<?= site_url('purchase-order/supplier/pos') ?>');
            const data = await res.json();
            if (!data.success) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-danger">${data.error || 'Failed to load purchase orders'}</td></tr>`;
                return;
            }

            currentPOs = data.purchase_orders || [];
            document.getElementById('pendingCount').textContent = data.counts?.awaiting_response ?? 0;
            document.getElementById('confirmedCount').textContent = data.counts?.confirmed ?? 0;
            document.getElementById('shippedCount').textContent = data.counts?.shipped ?? 0;
            document.getElementById('declinedCount').textContent = data.counts?.declined ?? 0;

            tbody.innerHTML = '';
            if (currentPOs.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-muted">No purchase orders found.</td></tr>';
                return;
            }

            currentPOs.forEach(po => {
                const row = document.createElement('tr');
                const meta = statusMeta(po.status);

                let actionsHTML = `<div class="btn-group btn-group-sm" role="group">`;
                if (po.status === 'PO_CREATED') {
                    actionsHTML += `
                        <button class="btn btn-success" onclick="showAcceptModal(${po.id})">Accept</button>
                        <button class="btn btn-warning" onclick="showChangesModal(${po.id})">Changes</button>
                        <button class="btn btn-danger" onclick="showDeclineModal(${po.id})">Decline</button>
                    `;
                } else if (po.status === 'SUPPLIER_CONFIRMED') {
                    actionsHTML += `
                        <button class="btn btn-info" onclick="showShipModal(${po.id})">Ship</button>
                    `;
                }
                actionsHTML += `
                    <button class="btn btn-outline-primary" onclick="viewDetails(${po.id})">Details</button>
                </div>`;

                row.innerHTML = `
                    <td><strong>${po.po_number}</strong></td>
                    <td>${po.total_items}</td>
                    <td>₱${parseFloat(po.total_amount || 0).toFixed(2)}</td>
                    <td><span class="status-badge ${meta.klass}">${meta.text}</span></td>
                    <td>${new Date(po.created_at).toLocaleString()}</td>
                    <td>${actionsHTML}</td>
                `;
                tbody.appendChild(row);
            });
        }

        function showAcceptModal(poId) {
            document.getElementById('acceptPoId').value = poId;
            document.getElementById('acceptNotes').value = '';
            new bootstrap.Modal(document.getElementById('acceptModal')).show();
        }

        function submitAccept() {
            const poId = document.getElementById('acceptPoId').value;
            const notes = document.getElementById('acceptNotes').value;
            
            console.log('Accept PO:', poId, 'Notes:', notes);
            
            fetch('<?= site_url('purchase-order/supplier-accept') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ po_id: parseInt(poId), notes: notes })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    bootstrap.Modal.getInstance(document.getElementById('acceptModal')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(err => console.error('Error:', err));
        }

        function showChangesModal(poId) {
            document.getElementById('changesPoId').value = poId;
            document.getElementById('changesReason').value = '';
            new bootstrap.Modal(document.getElementById('changesModal')).show();
        }

        function submitChanges() {
            const poId = document.getElementById('changesPoId').value;
            const reason = document.getElementById('changesReason').value;

            if (!reason) { alert('Please provide a reason'); return; }

            fetch('<?= site_url('purchase-order/supplier-request-changes') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ po_id: parseInt(poId), reason: reason })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    bootstrap.Modal.getInstance(document.getElementById('changesModal')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            });
        }

        function showDeclineModal(poId) {
            document.getElementById('declinePoId').value = poId;
            document.getElementById('declineReason').value = '';
            new bootstrap.Modal(document.getElementById('declineModal')).show();
        }

        function submitDecline() {
            const poId = document.getElementById('declinePoId').value;
            const reason = document.getElementById('declineReason').value;

            if (!reason) { alert('Please provide a reason'); return; }

            fetch('<?= site_url('purchase-order/supplier-decline') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ po_id: parseInt(poId), reason: reason })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    bootstrap.Modal.getInstance(document.getElementById('declineModal')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            });
        }

        function showShipModal(poId) {
            document.getElementById('shipPoId').value = poId;
            document.getElementById('trackingNumber').value = '';
            new bootstrap.Modal(document.getElementById('shipModal')).show();
        }

        function submitShip() {
            const poId = document.getElementById('shipPoId').value;
            const tracking = document.getElementById('trackingNumber').value;

            if (!tracking) { alert('Please provide tracking number'); return; }

            fetch('<?= site_url('purchase-order/supplier-ship') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ po_id: parseInt(poId), tracking_number: tracking })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    bootstrap.Modal.getInstance(document.getElementById('shipModal')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            });
        }

        function viewDetails(poId) {
            const po = currentPOs.find(p => p.id === poId);
            if (po) {
                alert('PO Details:\n' + JSON.stringify(po, null, 2));
            }
        }

        // Load POs on page load
        document.addEventListener('DOMContentLoaded', loadPOs);
    </script>
</body>
</html>
