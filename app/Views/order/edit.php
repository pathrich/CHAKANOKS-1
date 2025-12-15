<?php
// app/Views/order/edit.php
?>

<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <h1>Edit Order</h1>
            <p class="text-muted">Update items in your draft order and submit for approval</p>

            <form id="orderEditForm" class="mt-4">
                <input type="hidden" id="orderId" value="<?= esc($order['id']) ?>">

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

                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Additional Notes</h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" id="orderNotes" name="notes" placeholder="Any special instructions or notes for this order..." rows="3"><?= esc($order['notes'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="d-flex gap-2 mb-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
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
                <div class="col-md-12">
                    <input type="text" class="form-control form-control-sm item-notes" placeholder="Item notes (optional)">
                </div>
            </div>
        </div>
    </div>
</template>

<script>
let itemsData = [];
let isSubmittingForApproval = false;

const existingItems = <?= json_encode($order['items'] ?? []) ?>;

document.addEventListener('DOMContentLoaded', function() {
    loadItems().then(() => {
        if (existingItems && existingItems.length) {
            existingItems.forEach(it => addItemRow(it));
        } else {
            addItemRow();
        }
        updateOrderTotal();
    });
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

        // Ensure existing order items are still selectable (even if category data is missing)
        if (Array.isArray(existingItems)) {
            existingItems.forEach(it => {
                const id = parseInt(it.item_id);
                if (!id) return;
                const found = itemsData.find(x => parseInt(x.id) === id);
                if (!found) {
                    itemsData.push({
                        id: id,
                        name: it.name || 'Item #' + id,
                        sku: it.sku || '',
                    });
                }
            });
        }
    } catch (error) {
        console.error('Failed to load items:', error);
        alert('Failed to load items. Please refresh and try again.');
    }
}

function addItemRow(existing) {
    const template = document.getElementById('itemRowTemplate');
    const clone = template.content.cloneNode(true);

    const select = clone.querySelector('.item-select');
    const qtyInput = clone.querySelector('.item-qty');
    const priceInput = clone.querySelector('.item-price');
    const subtotalInput = clone.querySelector('.item-subtotal');
    const notesInput = clone.querySelector('.item-notes');

    const cutWrapper = clone.querySelector('.chicken-cut-wrapper');
    const cutSelect = clone.querySelector('.chicken-cut-select');

    itemsData.forEach(item => {
        const option = document.createElement('option');
        option.value = item.id;
        option.textContent = `${item.name} (${item.sku})`;
        select.appendChild(option);
    });

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
            setCutOptions(['Legs', 'Wings', 'Breast']);
            cutWrapper.classList.remove('d-none');
        } else if (isChicken && (isProcessed || isMarinated || isSeasoned)) {
            setCutOptions(['Marinated', 'Seasoned']);
            cutWrapper.classList.remove('d-none');
        } else {
            cutWrapper.classList.add('d-none');
            if (cutSelect) cutSelect.value = '';
        }
    });

    function updateSubtotal() {
        const subtotal = (parseFloat(qtyInput.value) || 0) * (parseFloat(priceInput.value) || 0);
        subtotalInput.value = subtotal.toFixed(2);
        updateOrderTotal();
    }

    qtyInput.addEventListener('change', updateSubtotal);
    priceInput.addEventListener('change', updateSubtotal);

    clone.querySelector('.remove-item-btn').addEventListener('click', function() {
        this.closest('.item-row').remove();
        updateOrderTotal();
    });

    // Populate existing values if provided
    if (existing) {
        if (existing.item_id) select.value = existing.item_id;
        if (existing.quantity) qtyInput.value = existing.quantity;
        if (existing.unit_price !== undefined && existing.unit_price !== null) priceInput.value = existing.unit_price;
        if (existing.notes && notesInput) notesInput.value = existing.notes;

        // Trigger cut logic
        select.dispatchEvent(new Event('change'));
        updateSubtotal();
    } else {
        updateSubtotal();
    }

    document.getElementById('itemsList').appendChild(clone);
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

document.getElementById('addItemBtn').addEventListener('click', () => addItemRow());

document.getElementById('submitBtn').addEventListener('click', function() {
    isSubmittingForApproval = true;
    document.getElementById('orderEditForm').dispatchEvent(new Event('submit'));
});

document.getElementById('orderEditForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const orderId = parseInt(document.getElementById('orderId').value);

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

    if (!items.length) {
        alert('Please add at least one item to the order');
        return;
    }

    try {
        const response = await fetch('<?= site_url('order/update') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                order_id: orderId,
                items: items,
                notes: document.getElementById('orderNotes').value || null
            })
        });

        const data = await response.json();
        if (!data.success) {
            alert('Error: ' + (data.error || 'Failed to update order'));
            isSubmittingForApproval = false;
            return;
        }

        if (isSubmittingForApproval) {
            const submitResp = await fetch('<?= site_url('order/submit') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ order_id: orderId })
            });

            const submitData = await submitResp.json();
            if (submitData.success) {
                alert('Order submitted for approval!');
                window.location.href = '<?= site_url('order') ?>';
            } else {
                alert('Error: ' + (submitData.error || 'Failed to submit order'));
            }
        } else {
            alert('Order updated!');
            window.location.href = '<?= site_url('order') ?>';
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while updating the order');
    }

    isSubmittingForApproval = false;
});
</script>

<?= $this->endSection() ?>
