# Supply Chain Workflow - Quick Start Testing Guide

## Prerequisites

- CodeIgniter 4.6.3 running
- MySQL database connected
- Users and roles already seeded (central_admin, branch_manager, inventory_staff, supplier)

## Quick Setup

### 1. Run Migrations
```bash
php spark migrate
```

Expected output:
```
Running all new migrations...
Running: (App) 2025-12-07-000004_App\Database\Migrations\CreatePurchaseOrdersAndSuppliers
Migrations complete.
```

### 2. Seed Sample Suppliers (Database Insert)
```sql
INSERT INTO suppliers (name, contact_name, contact_email, contact_phone, address) VALUES
('ABC Trading Company', 'John Smith', 'john@abctrading.com', '555-1001', '123 Main St'),
('XYZ Logistics Inc', 'Jane Doe', 'jane@xyzlogistics.com', '555-2002', '456 Oak Ave'),
('Prime Supplies Ltd', 'Bob Johnson', 'bob@primesupplies.com', '555-3003', '789 Pine Rd');
```

---

## Testing Scenarios

### Scenario 1: Full Happy Path (Approval → Confirmed → Shipped → Delivered)

#### Step 1: Login as Inventory Staff
- URL: `http://localhost/CHAKANOKS-1/public`
- Username: `staff` (or inventory_staff)
- Password: `password123`

#### Step 2: Submit Supply Request
- Go to supply request form (link on dashboard)
- Select items from dropdown
- Enter quantities (e.g., 5 units of Item A, 3 units of Item B)
- Click "Submit"
- Should see: "Supply request submitted successfully"

**Check database:**
```sql
SELECT * FROM supply_requests ORDER BY id DESC LIMIT 1;
SELECT * FROM supply_request_items WHERE supply_request_id = (SELECT MAX(id) FROM supply_requests);
SELECT * FROM notifications WHERE type = 'supply_request_created';
```

#### Step 3: Login as Admin
- Logout and login as `admin`
- Password: `password123`

#### Step 4: Admin Approves Request
- Go to `/supply-request` (Supply Requests dashboard)
- Click on pending request
- Click "Approve"
- Watch for modal/confirmation

**Check database:**
```sql
SELECT * FROM supply_requests WHERE id = YOUR_REQUEST_ID;
SELECT * FROM purchase_orders WHERE supply_request_id = YOUR_REQUEST_ID;
SELECT * FROM purchase_order_items WHERE purchase_order_id = (SELECT id FROM purchase_orders WHERE supply_request_id = YOUR_REQUEST_ID);
```

Expected: PO created with status `PO_CREATED`, items copied, notifications sent.

#### Step 5: Supplier Views PO
- Go to `/purchase-order/supplier-dashboard` (or via API)
- Should see newly created PO with status "PO Created"
- Click "Accept"
- Modal appears for optional notes
- Click "Confirm PO"

**Check database:**
```sql
SELECT * FROM purchase_orders WHERE id = YOUR_PO_ID;
```

Expected: Status changed to `SUPPLIER_CONFIRMED`, audit log entry created.

#### Step 6: Supplier Ships Order
- From supplier dashboard
- Click "Ship" button on the confirmed PO
- Enter tracking number (e.g., `TRACK20251207001`)
- Click "Mark as Shipped"

**Check database:**
```sql
SELECT * FROM purchase_orders WHERE id = YOUR_PO_ID;
```

Expected: Status = `SHIPPED`, tracking_number populated.

#### Step 7: Admin Marks Delivered
- Login as admin
- Go to `/purchase-order` (admin PO dashboard)
- Find the shipped PO
- Click "Details" → "Mark as Delivered"

**Check database - Inventory Updated:**
```sql
SELECT * FROM purchase_orders WHERE id = YOUR_PO_ID;
SELECT * FROM branch_stocks WHERE branch_id = YOUR_BRANCH_ID;
```

Expected: 
- PO status = `DELIVERED`
- branch_stocks quantities increased by PO quantities
- Notifications sent to manager and admin
- audit_logs entry for "po_delivered_inventory_updated"

---

### Scenario 2: Supplier Requests Changes

#### Step 1-4: Same as Scenario 1 up to "Admin Approves"

#### Step 5: Supplier Requests Changes
- From supplier dashboard, on "PO Created" status PO
- Click "Request Changes"
- Modal appears
- Enter reason: "Cannot supply Item B, please remove or substitute with Item C"
- Click "Request Changes"

**Check database:**
```sql
SELECT * FROM purchase_orders WHERE id = YOUR_PO_ID;
SELECT * FROM audit_logs WHERE action = 'supplier_requested_changes_on_po';
```

Expected: PO status = `SUPPLIER_REQUESTED_CHANGES`, admin notified

#### Step 6: Admin Negotiates
- Admin can manually update purchase_order_items
- Or create new PO with substitute item
- (Manual process for now, future: UI form)

---

### Scenario 3: Supplier Declines

#### Step 1-4: Same as Scenario 1 up to "Admin Approves"

#### Step 5: Supplier Declines
- From supplier dashboard, click "Decline"
- Modal appears
- Reason: "Out of stock until next month"
- Click "Decline PO"

**Check database:**
```sql
SELECT * FROM purchase_orders WHERE id = YOUR_PO_ID;
SELECT * FROM notifications WHERE related_id = YOUR_PO_ID AND type = 'supplier_action_on_po';
```

Expected: PO status = `SUPPLIER_DECLINED`, admin notified to reassign

#### Step 6: Admin Reassigns Supplier (Manual)
```sql
UPDATE purchase_orders SET supplier_id = 2, status = 'PO_CREATED' WHERE id = YOUR_PO_ID;
```

(Future: Add UI form for reassignment)

---

## API Testing (cURL Examples)

### Accept PO
```bash
curl -X POST http://localhost/CHAKANOKS-1/public/purchase-order/supplier-accept \
  -H "Content-Type: application/json" \
  -d '{"po_id": 1, "supplier_id": 1, "notes": "Will deliver Monday"}'
```

### Request Changes
```bash
curl -X POST http://localhost/CHAKANOKS-1/public/purchase-order/supplier-request-changes \
  -H "Content-Type: application/json" \
  -d '{"po_id": 1, "supplier_id": 1, "reason": "Need price adjustment"}'
```

### Ship Order
```bash
curl -X POST http://localhost/CHAKANOKS-1/public/purchase-order/supplier-ship \
  -H "Content-Type: application/json" \
  -d '{"po_id": 1, "tracking_number": "TRACK123456789"}'
```

### Mark as Delivered
```bash
curl -X POST http://localhost/CHAKANOKS-1/public/purchase-order/mark-delivered \
  -H "Content-Type: application/json" \
  -d '{"po_id": 1}'
```

---

## Database Queries for Verification

### Check Full Workflow Trail
```sql
SELECT 
    sr.id as request_id,
    sr.status as request_status,
    po.id as po_id,
    po.po_number,
    po.status as po_status,
    po.tracking_number,
    COUNT(DISTINCT poi.id) as item_count,
    SUM(poi.subtotal) as total_amount
FROM supply_requests sr
LEFT JOIN purchase_orders po ON po.supply_request_id = sr.id
LEFT JOIN purchase_order_items poi ON poi.purchase_order_id = po.id
WHERE sr.id = YOUR_REQUEST_ID
GROUP BY sr.id, sr.status, po.id, po.po_number, po.status, po.tracking_number;
```

### Check Inventory Changes
```sql
SELECT 
    bs.branch_id,
    b.name as branch_name,
    bs.item_id,
    i.name as item_name,
    bs.quantity as current_stock
FROM branch_stocks bs
JOIN branches b ON b.id = bs.branch_id
JOIN items i ON i.id = bs.item_id
WHERE bs.branch_id = YOUR_BRANCH_ID
ORDER BY bs.created_at DESC;
```

### Check Audit Trail
```sql
SELECT 
    al.id,
    al.action,
    al.details,
    u.full_name,
    al.created_at
FROM audit_logs al
LEFT JOIN users u ON u.id = al.user_id
WHERE al.action LIKE '%po%' OR al.action LIKE '%supplier%'
ORDER BY al.created_at DESC
LIMIT 20;
```

### Check Notifications Sent
```sql
SELECT 
    n.id,
    u.full_name as recipient,
    n.type,
    n.title,
    n.message,
    n.is_read,
    n.created_at
FROM notifications n
LEFT JOIN users u ON u.id = n.recipient_id
WHERE n.related_type = 'purchase_order'
ORDER BY n.created_at DESC
LIMIT 15;
```

---

## Troubleshooting

### Issue: PO not created on approval
**Check:**
```sql
SELECT * FROM suppliers;
```
- Ensure at least one supplier exists
- If no suppliers, insert one manually:
  ```sql
  INSERT INTO suppliers (name, contact_name, contact_email) 
  VALUES ('Test Supplier', 'Test Contact', 'test@supplier.com');
  ```

### Issue: Inventory not updated on delivery
**Check:**
```sql
SELECT * FROM purchase_order_items WHERE purchase_order_id = YOUR_PO_ID;
```
- Ensure items exist in the PO
- Check branch_stocks record:
  ```sql
  SELECT * FROM branch_stocks WHERE branch_id = YOUR_BRANCH_ID AND item_id = YOUR_ITEM_ID;
  ```

### Issue: Notifications not visible
**Check:**
```sql
SELECT * FROM notifications WHERE recipient_id = YOUR_USER_ID ORDER BY created_at DESC;
```
- Ensure recipient_id matches your user ID
- Check `is_read` status
- Verify `created_at` timestamp

### Issue: 403/Access Denied errors
- Ensure you're logged in: Check `session('user_id')`
- Verify user role: 
  ```sql
  SELECT r.name FROM user_roles ur
  JOIN roles r ON r.id = ur.role_id
  WHERE ur.user_id = YOUR_USER_ID;
  ```

---

## Expected Test Results

After running full Scenario 1:

| Table | Records | Status |
|-------|---------|--------|
| supply_requests | 1 | Approved |
| purchase_orders | 1 | DELIVERED |
| purchase_order_items | 2-5 | Complete |
| branch_stocks | Updated | Quantity += PO qty |
| notifications | 8-10 | Mixed read/unread |
| audit_logs | 6-8 | Full trail |

---

## Performance Tips

- For bulk testing, disable notifications temporarily
- Clear audit_logs between test cycles: `TRUNCATE audit_logs;`
- Use transaction rollback for isolated testing:
  ```bash
  php spark db:seed NameOfSeeder
  ```

---

Done! The workflow is ready to test. Start with **Scenario 1** for a complete end-to-end test.
