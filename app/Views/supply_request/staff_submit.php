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
                    <select class="form-control item-select" required>
                        <option value="">Select an item...</option>
                    </select>
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

// Load available items
async function loadItems() {
    try {
        const response = await fetch('/api/items');
        itemsData = await response.json();
    } catch (error) {
        console.error('Failed to load items:', error);
    }
}

// Initialize form
document.addEventListener('DOMContentLoaded', function() {
    loadItems();
    loadPreviousRequests();
    
    // Add first item row
    addItemRow();
});

// Add item row
function addItemRow() {
    const template = document.getElementById('itemRowTemplate');
    const clone = template.content.cloneNode(true);
    
    // Populate item select
    const select = clone.querySelector('.item-select');
    itemsData.forEach(item => {
        const option = document.createElement('option');
        option.value = item.id;
        option.textContent = `${item.name} (${item.sku})`;
        select.appendChild(option);
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
        const itemId = row.querySelector('.item-select').value;
        const quantity = parseInt(row.querySelector('.item-qty').value);
        const notes = row.querySelector('.item-notes').value;
        
        if (itemId && quantity > 0) {
            items.push({
                item_id: parseInt(itemId),
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
        const response = await fetch('/supply-request/submit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                items: items,
                notes: document.getElementById('requestNotes').value || null
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(`Supply request #${data.requestId} submitted successfully! Pending admin approval.`);
            document.getElementById('supplyRequestForm').reset();
            document.getElementById('itemsList').innerHTML = '';
            addItemRow();
            loadPreviousRequests();
        } else {
            alert('Error: ' + (data.error || 'Failed to submit request'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while submitting the request');
    }
});

// Load previous requests
async function loadPreviousRequests() {
    try {
        const response = await fetch('/supply-request/my-requests', {
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
        'Pending': 'warning',
        'Approved': 'success',
        'Rejected': 'danger',
        'Fulfilled': 'info'
    };
    return colors[status] || 'secondary';
}
</script>

<?= $this->endSection() ?>
