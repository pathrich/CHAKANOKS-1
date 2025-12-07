# Supply Chain Management System - Complete Workflow

## Overview

This document describes the complete supply chain workflow system that automates the process from staff submitting supply requests through admin approval, purchase order creation, supplier management, shipment tracking, delivery, and inventory updates.

---

## Workflow Steps

### Step 1: Staff Submits Supply Request

**Who:** Inventory Staff or Manager  
**Where:** Supply Request form (POST to `/supply-request/submit`)  
**What happens:**
- Staff selects items, quantities, and notes
- Request is created with status: **PENDING_ADMIN_APPROVAL**
- Audit log entry created
- Admin is notified

**Data stored in:** `supply_requests`, `supply_request_items`, `notifications`, `activity_logs`

---

### Step 2: Admin Reviews Request

**Who:** Central Admin  
**Where:** `/supply-request` admin dashboard  
**Admin chooses:**

#### Option A: APPROVE ✅
- Request status → **APPROVED**
- **NEW: Purchase Order (PO) is automatically created** with status **PO_CREATED**
- First available supplier is assigned
- PO number generated (format: `PO-YYYYMMDD-0001`)
- Notifications sent to:
  - **Manager/Branch:** "Your supply request was approved. PO created."
  - **Supplier:** "New PO received. Please confirm or request changes."
  - **Admin:** Audit log entry
- **Flow continues to Step 3**

#### Option B: REQUEST CHANGES
- Request status → **PENDING_MANAGER_EDIT**
- Manager notified with change request
- Manager can resubmit

#### Option C: REJECT
- Request status → **REJECTED**
- Manager notified with rejection reason

**Data flow:**
```
supply_requests (status → APPROVED)
  ↓
purchase_orders (auto-created, status → PO_CREATED)
  ↓
purchase_order_items (items copied from supply request)
  ↓
notifications (sent to manager, supplier, admin)
  ↓
audit_logs (recorded)
```

---

### Step 3: Supplier Responds to PO

**Who:** Supplier  
**Where:** Supplier dashboard (API endpoints POST)  
**Supplier chooses:**

#### Option A: ACCEPT / CONFIRM ✅
- PO status → **SUPPLIER_CONFIRMED**
- Supplier can provide delivery notes
- Admin & Manager notified: "Supplier confirmed your order"
- **Supplier can now proceed to ship**

#### Option B: REQUEST CHANGES
- PO status → **SUPPLIER_REQUESTED_CHANGES**
- Admin & Manager notified with supplier's change request
- Admin can modify PO or reassign supplier

#### Option C: DECLINE
- PO status → **SUPPLIER_DECLINED**
- Admin notified: "Supplier declined. Please reassign."
- Admin selects new supplier manually (future enhancement)

**Data stored in:** `purchase_orders` (status updated), `notifications`, `audit_logs`

---

### Step 4: Supplier Ships Items

**Who:** Supplier  
**When:** After confirming order  
**Where:** POST `/purchase-order/supplier-ship`  
**What happens:**
- PO status → **SHIPPED**
- Tracking number recorded
- Notifications sent to:
  - **Manager/Branch:** "Your order is on the way! Tracking: XYZ123"
  - **Admin:** "Order shipped with tracking number"
- **Now awaiting delivery**

**Data stored in:** `purchase_orders` (tracking_number, status), `notifications`, `audit_logs`

---

### Step 5: Admin Marks Delivery & Updates Inventory

**Who:** Admin or Manager  
**Where:** PO dashboard - "Mark as Delivered" button  
**What happens:**
- PO status → **DELIVERED**
- **Inventory automatically updated:**
  - For each item in PO:
    - Find `branch_stocks` record for this branch + item
    - Add PO quantity to existing stock
    - Create new `branch_stocks` record if doesn't exist
- Notifications sent to:
  - **Manager/Branch:** "Order delivered. Inventory updated."
  - **Admin:** "Inventory updated for branch X"
- Audit log entry recorded

**Data updated in:**
```
purchase_orders (status → DELIVERED)
  ↓
branch_stocks (quantity += po_qty)
  ↓
notifications (sent to manager, admin)
  ↓
audit_logs (recorded with details)
```

---

## Database Schema

### Core Tables

#### `suppliers`
```sql
id, name, contact_name, contact_email, contact_phone, address, created_at, updated_at
```

#### `purchase_orders`
```sql
id, supply_request_id (FK), supplier_id (FK), created_by (FK), 
status (ENUM: PO_CREATED, SUPPLIER_CONFIRMED, SUPPLIER_REQUESTED_CHANGES, SUPPLIER_DECLINED, SHIPPED, DELIVERED, COMPLETED, CANCELLED),
po_number (unique), total_items, total_amount, tracking_number, notes, created_at, updated_at
```

#### `purchase_order_items`
```sql
id, purchase_order_id (FK), item_id (FK), quantity, unit_price, subtotal, notes
```

#### `audit_logs`
```sql
id, user_id (FK), role, action, details, created_at
```

### Example Status Flow
```
PO_CREATED 
  → SUPPLIER_CONFIRMED 
    → SHIPPED 
      → DELIVERED 
        → COMPLETED
```

Or:
```
PO_CREATED 
  → SUPPLIER_REQUESTED_CHANGES (→ admin negotiates)
  → SUPPLIER_DECLINED (→ admin reassigns supplier)
```

---

## API Endpoints

### Purchase Order Endpoints

#### Admin Operations
- `GET /purchase-order` - List all POs with details
- `POST /purchase-order/mark-delivered` - Mark PO as delivered and update inventory

#### Supplier Operations
- `POST /purchase-order/supplier-accept` - Supplier confirms PO
  ```json
  { "po_id": 1, "supplier_id": 1, "notes": "Will deliver in 5 days" }
  ```

- `POST /purchase-order/supplier-request-changes` - Supplier requests modifications
  ```json
  { "po_id": 1, "supplier_id": 1, "reason": "Cannot supply item X" }
  ```

- `POST /purchase-order/supplier-decline` - Supplier declines order
  ```json
  { "po_id": 1, "supplier_id": 1, "reason": "Out of stock" }
  ```

- `POST /purchase-order/supplier-ship` - Mark items as shipped
  ```json
  { "po_id": 1, "tracking_number": "TRACKING123456" }
  ```

---

## Models

### `PurchaseOrderModel`
**Key Methods:**
- `createFromSupplyRequest($supplyRequestId, $supplierId, $items, $createdBy, $notes)`
  - Creates PO with line items in transaction
  - Calculates totals
  - Generates unique PO number
  - Returns full PO record

- `generatePONumber()`
  - Format: `PO-YYYYMMDD-0001`
  - Increments for each PO created on same day

### `SupplierModel`
- Basic CRUD for supplier information

### `AuditLogModel`
- `log($action, $details, $userId, $role)`
- Records every action with timestamp

### `SupplyRequestModel` (Enhanced)
**New Methods:**
- `notifySupplier($supplierId, $po)` - Sends notification to supplier
- `notifyFranchise($request, $po)` - Sends notification to branch/franchise
- Integration with PurchaseOrder creation on approval

---

## Controllers

### `PurchaseOrder` Controller
**Methods:**
- `index()` - Admin dashboard with all POs
- `supplierAccept()` - Supplier confirms PO
- `supplierRequestChanges()` - Supplier requests modifications
- `supplierDecline()` - Supplier declines order
- `supplierShip()` - Supplier marks as shipped with tracking
- `markDelivered()` - Admin completes delivery and updates inventory

### `SupplyRequest` Controller (Enhanced)
- `approveRequest()` now automatically creates PO

---

## Views

### Admin Dashboard: `purchase_order/admin_dashboard.php`
- Bootstrap 5 responsive table
- Shows all POs with status badges
- Color-coded statuses
- Modal for detailed PO view
- Button to mark as delivered
- Supplier reassignment option (stub)

### Supplier Dashboard: `purchase_order/supplier_dashboard.php`
- Bootstrap 5 responsive UI
- Statistics cards (pending, confirmed, shipped, declined)
- Table of assigned POs
- Modal forms for:
  - Accept with delivery notes
  - Request changes with reason
  - Decline with reason
  - Ship with tracking number
- Real-time status updates

---

## Notifications

### Who Gets Notified?

| Event | Recipient | Type | Trigger |
|-------|-----------|------|---------|
| Supply request approved | Manager | supply_request_approved | Admin approves |
| PO created | Supplier | purchase_order_created | Auto on approve |
| PO created | Manager | purchase_order_created | Auto on approve |
| Supplier confirmed | Admin | supplier_action_on_po | Supplier confirms |
| Supplier requested changes | Admin | supplier_action_on_po | Supplier requests |
| Supplier declined | Admin | supplier_action_on_po | Supplier declines |
| Order shipped | Manager | po_shipped | Supplier ships |
| Order delivered | Manager | po_delivered | Admin marks delivered |
| Order delivered | Admin | po_delivered | Admin marks delivered |

### Notification Schema
```sql
id, recipient_id (FK users), type, title, message, 
related_id, related_type, is_read, created_at, read_at
```

---

## Audit Trail

Every action is logged in `audit_logs` table:

```
action: 'supply_request_submitted' → details: "Supply request #123 submitted..."
action: 'supply_request_approved' → details: "Supply request #123 approved..."
action: 'purchase_order_created' → details: {"po_id": 456, "supply_request_id": 123}
action: 'supplier_confirmed_po' → details: "Supplier 1 confirmed PO 456..."
action: 'po_shipped' → details: "PO 456 shipped with tracking: TRACK123"
action: 'po_delivered_inventory_updated' → details: "PO 456 delivered. Inventory updated for branch 5"
```

---

## Inventory Update Logic

When admin marks PO as DELIVERED:

```php
FOR EACH item in purchase_order_items:
    qty = item.quantity
    
    CHECK branch_stocks for (branch_id, item_id):
        IF EXISTS:
            UPDATE quantity += qty
        ELSE:
            INSERT new record with quantity = qty
    
UPDATE purchase_orders SET status = 'DELIVERED'
NOTIFY manager, admin
LOG audit entry
```

---

## Transaction Safety

All critical operations use database transactions:
- PO creation
- Inventory update on delivery
- Supplier action updates

Failures rollback all changes atomically.

---

## Migration

Run migrations to create all required tables:

```bash
php spark migrate
```

This creates:
- `suppliers` table
- `purchase_orders` table
- `purchase_order_items` table
- `audit_logs` table

---

## Testing the Workflow

### 1. Create Suppliers (via database)
```sql
INSERT INTO suppliers (name, contact_name, contact_email, contact_phone) 
VALUES ('ABC Corp', 'John Doe', 'john@abc.com', '555-1234');
```

### 2. Submit Supply Request
- Login as `inventory_staff` or `branch_manager`
- Go to supply request form
- Select items, quantities
- Submit

### 3. Admin Approves
- Login as `central_admin`
- View pending requests
- Click Approve
- PO auto-created, supplier notified

### 4. Supplier Responds
- Access supplier portal
- View PO
- Click "Accept" (or request changes/decline)
- Note status changes

### 5. Supplier Ships
- From supplier portal
- Click "Ship"
- Enter tracking number
- Manager gets notified

### 6. Admin Marks Delivered
- From PO admin dashboard
- Click "Mark as Delivered"
- Inventory automatically updated
- Branch manager notified

### 7. Check Inventory
- Navigate to inventory
- Verify quantities increased for the branch

---

## Files Created/Modified

### New Files
- `app/Database/Migrations/2025-12-07-000004_CreatePurchaseOrdersAndSuppliers.php`
- `app/Models/SupplierModel.php`
- `app/Models/PurchaseOrderModel.php`
- `app/Models/AuditLogModel.php`
- `app/Controllers/PurchaseOrder.php`
- `app/Views/purchase_order/admin_dashboard.php`
- `app/Views/purchase_order/supplier_dashboard.php`

### Modified Files
- `app/Models/SupplyRequestModel.php` (added notification methods, PO creation integration)
- `app/Config/Routes.php` (added PO routes)

---

## Future Enhancements

- [ ] Email notifications integration
- [ ] Supplier reassignment via UI
- [ ] Partial delivery support
- [ ] Invoice matching against PO
- [ ] Payment tracking
- [ ] Multi-supplier comparison for price optimization
- [ ] Historical reporting and analytics
- [ ] Supplier performance metrics

---

## Summary

The workflow is **fully automated** from supply request through final delivery and inventory update:

```
Staff Request → Admin Approval → PO Created → Supplier Notified → Supplier Response 
→ Shipment → Delivery → Inventory Updated → Complete
```

Every step is tracked, audited, and notifications keep all stakeholders informed.
