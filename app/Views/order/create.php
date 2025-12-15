<?php
// app/Views/order/create.php
?>

<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <h1>Create New Order</h1>
            <p class="text-muted">Add items to your order and submit for approval</p>

            <form id="orderForm" class="mt-4">
                <!-- Items Section -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Order Items</h5>
                    </div>
                    <div class="card-body">
                        <div id="itemsList"></div>
                        <button type="button" class="btn btn-outline-primary" id="addItemBtn">
                            + Add Item
                        </button>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Total Items:</strong> <span id="totalItems">0</span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Total Amount:</strong> ₱<span id="totalAmount">0.00</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Additional Notes</h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" id="orderNotes" name="notes" 
                                  placeholder="Any special instructions or notes for this order..." 
                                  rows="3"></textarea>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="d-flex gap-2 mb-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save as Draft
                    </button>
                    <button type="submit" class="btn btn-success" id="submitBtn">
                        <i class="fas fa-paper-plane"></i> Save & Submit for Approval
                    </button>
                    <a href="<?= site_url('order') ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Item Row Template -->
<template id="itemRowTemplate">
    <div class="item-row card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">Item</label>
                    <select class="form-control item-select" required>
                        <option value="">Select an item...</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Qty</label>
                    <input type="number" class="form-control item-qty" min="1" value="1" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Unit Price</label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" class="form-control item-price" min="0" step="0.01" value="0.00" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Subtotal</label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="text" class="form-control item-subtotal" readonly value="0.00">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-sm btn-danger remove-item-btn w-100">Remove</button>
                </div>
            </div>
            <div class="row g-2 mt-2">
                <div class="col-md-4 d-none chicken-cut-wrapper">
                    <select class="form-select form-select-sm chicken-cut-select">
                        <option value="">Select option...</option>
                    </select>
                </div>
                <div class="col-md-<?= 12 ?>">
                    <input type="text" class="form-control form-control-sm item-notes" placeholder="Item notes (optional)">
                </div>
            </div>
        </div>
    </div>
</template>

<script>
let itemCount = 0;
let itemsData = [];

// Load available items on page load
document.addEventListener('DOMContentLoaded', async function() {
    await loadItems();
    addItemRow();
});

async function loadItems() {
    try {
        const response = await fetch('<?= site_url('api/items') ?>', {
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`Failed to load items (${response.status})`);
        }

        const allItems = await response.json();
        if (!Array.isArray(allItems)) {
            throw new Error('Invalid items response');
        }
        const excludedSkus = new Set(['SKU-APP-001', 'SKU-BEF-001', 'SKU-MLK-001']);

        itemsData = allItems.filter(it => {
            const sku = String(it.sku || '').trim();
            return !excludedSkus.has(sku);
        });
    } catch (error) {
        console.error('Failed to load items:', error);
        alert('Failed to load items. Please refresh and try again.');
    }
}

function addItemRow() {
    const template = document.getElementById('itemRowTemplate');
    const clone = template.content.cloneNode(true);
    
    // Populate item select
    const select = clone.querySelector('.item-select');
    const cutWrapper = clone.querySelector('.chicken-cut-wrapper');
    const cutSelect = clone.querySelector('.chicken-cut-select');

    itemsData.forEach(item => {
        const option = document.createElement('option');
        option.value = item.id;
        option.textContent = `${item.name} (${item.sku})`;
        select.appendChild(option);
    });

    // Configure dynamic dropdown for Chicken Cuts and Processed Chicken
    function setCutOptions(options) {
        if (!cutSelect) return;
        cutSelect.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Select option...';
        cutSelect.appendChild(placeholder);

        options.forEach(label => {
            const opt = document.createElement('option');
            opt.value = label;
            opt.textContent = label;
            cutSelect.appendChild(opt);
        });
    }

    // Show dropdown and populate options based on selected item
    select.addEventListener('change', function() {
        const selectedItem = itemsData.find(i => i.id == this.value);
        if (!selectedItem || !selectedItem.name) {
            cutWrapper.classList.add('d-none');
            if (cutSelect) cutSelect.value = '';
            return;
        }

        const name = selectedItem.name.toLowerCase();

        const isChicken = name.includes('chicken');
        const isCut = name.includes('cut');
        const isProcessed = name.includes('processed');
        const isMarinated = name.includes('marinated');
        const isSeasoned = name.includes('seasoned');

        if (isChicken && isCut) {
            // Chicken cuts (Legs, Wings, Breast)
            setCutOptions(['Legs', 'Wings', 'Breast']);
            cutWrapper.classList.remove('d-none');
        } else if (isChicken && (isProcessed || isMarinated || isSeasoned)) {
            // Processed chicken (Marinated, Seasoned)
            setCutOptions(['Marinated', 'Seasoned']);
            cutWrapper.classList.remove('d-none');
        } else {
            cutWrapper.classList.add('d-none');
            if (cutSelect) cutSelect.value = '';
        }
    });
    
    // Calculate subtotal when qty or price changes
    const qtyInput = clone.querySelector('.item-qty');
    const priceInput = clone.querySelector('.item-price');
    const subtotalInput = clone.querySelector('.item-subtotal');
    
    function updateSubtotal() {
        const subtotal = (parseFloat(qtyInput.value) || 0) * (parseFloat(priceInput.value) || 0);
        subtotalInput.value = subtotal.toFixed(2);
        updateOrderTotal();
    }
    
    qtyInput.addEventListener('change', updateSubtotal);
    priceInput.addEventListener('change', updateSubtotal);
    
    // Remove button
    clone.querySelector('.remove-item-btn').addEventListener('click', function() {
        this.closest('.item-row').remove();
        updateOrderTotal();
    });
    
    document.getElementById('itemsList').appendChild(clone);
    itemCount++;
}

function updateOrderTotal() {
    let totalItems = 0;
    let totalAmount = 0;
    
    document.querySelectorAll('.item-row').forEach(row => {
        const qty = parseInt(row.querySelector('.item-qty').value) || 0;
        const subtotal = parseFloat(row.querySelector('.item-subtotal').value) || 0;
        
        totalItems += qty;
        totalAmount += subtotal;
    });
    
    document.getElementById('totalItems').textContent = totalItems;
    document.getElementById('totalAmount').textContent = totalAmount.toFixed(2);
}

// Add item button
document.getElementById('addItemBtn').addEventListener('click', addItemRow);

// Form submission
let isSubmittingForApproval = false;

document.getElementById('submitBtn').addEventListener('click', function(e) {
    isSubmittingForApproval = true;
    document.getElementById('orderForm').dispatchEvent(new Event('submit'));
});

document.getElementById('orderForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Collect items
    const items = [];
    document.querySelectorAll('.item-row').forEach(row => {
        const itemId = row.querySelector('.item-select').value;
        const quantity = parseInt(row.querySelector('.item-qty').value);
        const unitPrice = parseFloat(row.querySelector('.item-price').value);
        const notesInput = row.querySelector('.item-notes');
        const cutSelect = row.querySelector('.chicken-cut-select');
        let notes = notesInput ? notesInput.value : '';

        if (cutSelect && cutSelect.value) {
            const cutNote = `Cut: ${cutSelect.value}`;
            notes = notes ? `${cutNote}; ${notes}` : cutNote;
        }
        
        if (itemId && quantity > 0 && unitPrice >= 0) {
            items.push({
                item_id: parseInt(itemId),
                quantity: quantity,
                unit_price: unitPrice,
                notes: notes || null
            });
        }
    });
    
    if (items.length === 0) {
        alert('Please add at least one item to the order');
        return;
    }
    
    try {
        const response = await fetch('<?= site_url('order/store') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                items: items,
                notes: document.getElementById('orderNotes').value || null
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (isSubmittingForApproval) {
                // Submit for approval
                await submitOrder(data.orderId);
            } else {
                // Just save as draft
                alert(`Order ${data.orderNumber} saved as draft!`);
                window.location.href = '<?= site_url('order') ?>';
            }
        } else {
            alert('Error: ' + (data.error || 'Failed to create order'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while creating the order');
    }
    
    isSubmittingForApproval = false;
});

async function submitOrder(orderId) {
    try {
        const response = await fetch('<?= site_url('order/submit') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ order_id: orderId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Order submitted for approval!');
            window.location.href = '<?= site_url('order') ?>';
        } else {
            alert('Error: ' + (data.error || 'Failed to submit order'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while submitting the order');
    }
}
</script>

<?= $this->endSection() ?>
