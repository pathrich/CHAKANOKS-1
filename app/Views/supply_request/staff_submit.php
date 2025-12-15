<?php
// app/Views/supply_request/staff_submit.php
?>

<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h1>Submit Supply Request</h1>
            <p class="text-muted">Request additional stock for your branch</p>

            <form id="supplyRequestForm">
                <!-- Items Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Request Items</h5>
                    </div>
                    <div class="card-body">
                        <div id="itemsList"></div>
                        <button type="button" class="btn btn-outline-primary" id="addItemBtn">
                            + Add Item
                        </button>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Supplier & Delivery</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Preferred Supplier (optional)</label>
                                <select class="form-select" id="preferredSupplier">
                                    <option value="">-- None --</option>
                                    <?php foreach (($suppliers ?? []) as $s): ?>
                                        <option value="<?= (int) $s['id'] ?>"><?= esc($s['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Required Delivery Date (optional)</label>
                                <input type="date" class="form-control" id="requiredDeliveryDate">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Additional Notes</h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" id="requestNotes" name="notes" 
                                  placeholder="Any additional notes or special requirements..." 
                                  rows="3"></textarea>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                    <button type="reset" class="btn btn-outline-secondary">Clear</button>
                </div>
            </form>

            <!-- Previous Requests -->
            <div class="mt-5">
                <h4>Your Previous Requests</h4>
                <div id="previousRequests">Loading...</div>
            </div>

            <?php if (in_array('branch_manager', (array) (session('user_roles') ?? []), true) || (session('user_role') === 'branch_manager')): ?>
                <div class="mt-5">
                    <h4>Requests Awaiting Your Approval</h4>
                    <div id="pendingBranchApprovals" class="text-muted">Loading...</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Item Row Template -->
<template id="itemRowTemplate">
    <div class="item-row card mb-2">
        <div class="card-body">
            <div class="row align-items-end g-2">
                <div class="col-md-5">
                    <label class="form-label">Item</label>
                    <input type="text" class="form-control item-name" list="itemsDatalist" placeholder="Type item name or SKU" required>
                    <input type="hidden" class="item-id">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" class="form-control item-qty" min="1" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Notes</label>
                    <input type="text" class="form-control item-notes" placeholder="Optional">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-sm btn-danger remove-item-btn">Remove</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
let itemCount = 0;
let itemsData = [];

// Precomputed endpoint URLs (respect base URL)
const SUPPLY_ITEMS_URL = '<?= site_url('api/items') ?>';
const SUPPLY_SUBMIT_URL = '<?= site_url('supply-request/submit') ?>';
const SUPPLY_MY_REQUESTS_URL = '<?= site_url('supply-request/my-requests') ?>';
const SUPPLY_PENDING_BRANCH_URL = '<?= site_url('supply-request/pending-branch') ?>';
const SUPPLY_BRANCH_APPROVE_URL = '<?= site_url('supply-request/branch-approve') ?>';
const SUPPLY_BRANCH_REJECT_URL = '<?= site_url('supply-request/branch-reject') ?>';

// Load available items and build datalist for type-ahead suggestions
async function loadItems() {
    try {
        const response = await fetch(SUPPLY_ITEMS_URL);
        itemsData = await response.json();

        const datalist = document.getElementById('itemsDatalist');
        if (datalist) {
            datalist.innerHTML = '';
            itemsData.forEach(item => {
                const option = document.createElement('option');
                option.value = `${item.name} (${item.sku})`;
                datalist.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Failed to load items:', error);
    }
}

// Initialize form
document.addEventListener('DOMContentLoaded', function() {
    loadItems();
    loadPreviousRequests();
    loadPendingBranchApprovals();
    
    // Add first item row
    addItemRow();
});

// Add item row
function addItemRow() {
    const template = document.getElementById('itemRowTemplate');
    const clone = template.content.cloneNode(true);

    const nameInput = clone.querySelector('.item-name');
    const idInput = clone.querySelector('.item-id');

    // When user changes the typed item, try to map it to a known item and store its ID
    nameInput.addEventListener('change', function() {
        const label = this.value.trim();
        if (!label) {
            idInput.value = '';
            return;
        }

        // 1) Exact "Name (SKU)" match (what datalist uses)
        let match = itemsData.find(it => `${it.name} (${it.sku})` === label);

        // 2) Fallback: exact name match
        if (!match) {
            match = itemsData.find(it => it.name === label);
        }

        // 3) Fallback: case-insensitive name starts-with / contains
        if (!match) {
            const lower = label.toLowerCase();
            match = itemsData.find(it =>
                (it.name && it.name.toLowerCase().startsWith(lower)) ||
                (it.name && it.name.toLowerCase() === lower)
            );
        }

        if (match) {
            idInput.value = match.id;
        } else {
            idInput.value = '';
        }
    });
    
    // Remove button handler
    clone.querySelector('.remove-item-btn').addEventListener('click', function() {
        this.closest('.item-row').remove();
    });
    
    document.getElementById('itemsList').appendChild(clone);
    itemCount++;
}

// Add Item button
document.getElementById('addItemBtn').addEventListener('click', addItemRow);

// Form submit
document.getElementById('supplyRequestForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Collect items
    const items = [];
    document.querySelectorAll('.item-row').forEach(row => {
        let itemId = parseInt(row.querySelector('.item-id').value);
        const nameValue = row.querySelector('.item-name').value.trim();
        const quantity = parseInt(row.querySelector('.item-qty').value);
        const notes = row.querySelector('.item-notes').value;

        // If itemId is not set (e.g. user typed name and submitted immediately),
        // try to resolve it from the typed name using the same relaxed matching logic
        if (!itemId && nameValue) {
            let match = itemsData.find(it => `${it.name} (${it.sku})` === nameValue);
            if (!match) {
                match = itemsData.find(it => it.name === nameValue);
            }
            if (!match) {
                const lower = nameValue.toLowerCase();
                match = itemsData.find(it =>
                    (it.name && it.name.toLowerCase().startsWith(lower)) ||
                    (it.name && it.name.toLowerCase() === lower)
                );
            }
            if (match) {
                itemId = parseInt(match.id);
            }
        }
        
        if ((itemId || nameValue) && quantity > 0) {
            items.push({
                item_id: itemId,
                name: nameValue || null,
                quantity: quantity,
                notes: notes || null
            });
        }
    });
    
    if (items.length === 0) {
        alert('Please add at least one item');
        return;
    }
    
    try {
        const response = await fetch(SUPPLY_SUBMIT_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                items: items,
                notes: document.getElementById('requestNotes').value || null,
                preferred_supplier_id: document.getElementById('preferredSupplier') ? (document.getElementById('preferredSupplier').value || null) : null,
                required_delivery_date: document.getElementById('requiredDeliveryDate') ? (document.getElementById('requiredDeliveryDate').value || null) : null
            })
        });

        let data;
        try {
            data = await response.json();
        } catch (parseErr) {
            // Response is not JSON (likely HTML error / redirect) – show status and snippet
            const text = await response.text();
            console.error('Non-JSON response from /supply-request/submit:', text);
            alert(`Server error (${response.status}). Details: ` + text.substring(0, 200));
            return;
        }

        if (!response.ok) {
            alert('Error: ' + (data.error || `Request failed with status ${response.status}`));
            return;
        }

        if (data.success) {
            alert(`Supply request #${data.requestId} submitted successfully! Pending branch approval.`);
            document.getElementById('supplyRequestForm').reset();
            document.getElementById('itemsList').innerHTML = '';
            addItemRow();
            loadPreviousRequests();
            loadPendingBranchApprovals();
        } else {
            alert('Error: ' + (data.error || 'Failed to submit request'));
        }
    } catch (error) {
        console.error('Error submitting supply request:', error);
        alert('Network or server error while submitting the request. Please check your connection or contact admin.');
    }
});

// Load previous requests
async function loadPreviousRequests() {
    try {
        const response = await fetch(SUPPLY_MY_REQUESTS_URL, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        const container = document.getElementById('previousRequests');
        
        if (data.requests && data.requests.length > 0) {
            let html = '<table class="table table-sm">';
            html += '<thead><tr><th>Request #</th><th>Date</th><th>Items</th><th>Status</th></tr></thead><tbody>';
            
            data.requests.forEach(req => {
                const date = new Date(req.created_at).toLocaleDateString();
                const statusBadge = `<span class="badge bg-${getStatusColor(req.status)}">${req.status}</span>`;
                html += `<tr>
                    <td>#${req.id}</td>
                    <td>${date}</td>
                    <td>${req.total_items}</td>
                    <td>${statusBadge}</td>
                </tr>`;
            });
            
            html += '</tbody></table>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p class="text-muted">No previous requests</p>';
        }
    } catch (error) {
        console.error('Error loading previous requests:', error);
    }
}

function getStatusColor(status) {
    const colors = {
        'Pending Branch Approval': 'warning',
        'Pending Central Approval': 'warning',
        'Approved': 'success',
        'Rejected By Branch': 'danger',
        'Rejected By Central': 'danger',
        'Fulfilled': 'info'
    };
    return colors[status] || 'secondary';
}

async function loadPendingBranchApprovals() {
    const container = document.getElementById('pendingBranchApprovals');
    if (!container) {
        return;
    }

    container.innerHTML = 'Loading...';

    try {
        const res = await fetch(SUPPLY_PENDING_BRANCH_URL, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        let data;
        try {
            data = await res.json();
        } catch (parseErr) {
            const text = await res.text();
            console.error('Non-JSON response from /supply-request/pending-branch:', text);
            container.innerHTML = `<div class="alert alert-danger">Failed to load requests (HTTP ${res.status}).</div>`;
            return;
        }

        if (!res.ok || !data.success) {
            container.innerHTML = `<div class="alert alert-danger">${data.error || `Failed to load requests (HTTP ${res.status})`}</div>`;
            return;
        }

        const requests = data.requests || [];
        if (requests.length === 0) {
            container.innerHTML = '<p class="text-muted">No requests awaiting approval.</p>';
            return;
        }

        let html = '';
        requests.forEach(r => {
            const items = r.items || [];
            html += `<div class="card mb-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>Request #${r.id}</strong>
                            <div class="text-muted">Requested by: ${escapeHtml(r.requester_name || '')}</div>
                        </div>
                        <span class="badge bg-warning">Pending Branch Approval</span>
                    </div>
                    <div class="mt-2">
                        <table class="table table-sm mb-2">
                            <thead><tr><th>Item</th><th class="text-end">Qty</th></tr></thead>
                            <tbody>
                                ${items.map(it => `<tr><td>${escapeHtml(it.name || '')}</td><td class="text-end">${it.quantity_requested || ''}</td></tr>`).join('')}
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-success" data-action="approve" data-id="${r.id}">Approve & Send to Central</button>
                        <button class="btn btn-sm btn-danger" data-action="reject" data-id="${r.id}">Reject</button>
                    </div>
                </div>
            </div>`;
        });

        container.innerHTML = html;

        container.querySelectorAll('button[data-action]').forEach(btn => {
            btn.addEventListener('click', async function() {
                const action = this.getAttribute('data-action');
                const id = this.getAttribute('data-id');
                if (!id) return;

                if (action === 'approve') {
                    const res = await fetch(SUPPLY_BRANCH_APPROVE_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ request_id: parseInt(id, 10), notes: null })
                    });
                    const out = await res.json();
                    if (!out.success) {
                        alert('Error: ' + (out.error || 'Failed to approve'));
                        return;
                    }
                    alert('Approved and forwarded to Central Office.');
                    loadPendingBranchApprovals();
                    loadPreviousRequests();
                    return;
                }

                if (action === 'reject') {
                    const reason = prompt('Reason for rejection:');
                    if (!reason || !reason.trim()) {
                        return;
                    }

                    const res = await fetch(SUPPLY_BRANCH_REJECT_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ request_id: parseInt(id, 10), reason: reason })
                    });
                    const out = await res.json();
                    if (!out.success) {
                        alert('Error: ' + (out.error || 'Failed to reject'));
                        return;
                    }
                    alert('Rejected.');
                    loadPendingBranchApprovals();
                    loadPreviousRequests();
                }
            });
        });
    } catch (e) {
        console.error(e);
        container.innerHTML = '<div class="alert alert-danger">Failed to load requests.</div>';
    }
}
</script>

<?= $this->endSection() ?>
