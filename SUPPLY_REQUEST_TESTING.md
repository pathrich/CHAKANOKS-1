# Supply Request System - Quick Start & Testing Guide

## System Flow

```
┌─────────────────────────────────────────────────────────────┐
│ SUPPLY REQUEST WORKFLOW                                      │
└─────────────────────────────────────────────────────────────┘

1. STAFF SUBMITS REQUEST
   ├─ Logs into system
   ├─ Navigates to "Submit Supply Request"
   ├─ Selects items and quantities
   └─ Clicks "Submit Request"
       └─> Status: PENDING (logged in database)

2. ADMIN VIEWS REQUESTS
   ├─ Navigates to "/supply-request" dashboard
   ├─ Sees all PENDING requests in cards
   ├─ Views request details and line items
   └─ Can APPROVE or REJECT

3. ADMIN APPROVES
   ├─ Clicks "Approve" button
   ├─ Modal opens (optional notes)
   ├─ Clicks "Approve" in modal
   └─> Status: APPROVED
       └─> AUTOMATIC: Branch manager receives notification

4. BRANCH MANAGER NOTIFIED
   ├─ Next time manager logs in
   ├─ Sees unread notification badge
   ├─ Can view notification details
   └─ Knows: "Supply request #X has been approved"
```

---

## User Roles & Permissions

| Role | Can Submit | Can Approve | Receives Notifications |
|------|-----------|-------------|----------------------|
| **Staff** | ✅ | ❌ | ❌ |
| **Manager** (Branch) | ❌ | ❌ | ✅ (when approved) |
| **Central Admin** | ❌ | ✅ | ❌ |

---

## Testing the System

### Prerequisite Setup
```bash
# Run migrations
php spark migrate

# Seed demo data
php spark db:seed PrelimSeeder
```

Default test users (password: `password123`):
- `admin` - Central Admin (can approve)
- `manager` - Branch Manager (receives notifications)
- `staff` - Inventory Staff (can submit)

---

### Test Case 1: Staff Submits Request

**Actor:** staff user (branch_id = 1)

**Steps:**

1. Login as `staff` / `password123`
2. Navigate to: `/supply-request/staff-submit` (URL to add to dashboard)
3. Form appears with item selection
4. Add 2 items:
   - Organic Apples (SKU-APP-001): Qty 10
   - Ground Beef (SKU-BEF-001): Qty 5
5. Add notes: "Low stock alert"
6. Click "Submit Request"

**Expected Result:**
- ✅ Alert: "Supply request #1 submitted successfully!"
- ✅ Form resets
- ✅ "Your Previous Requests" shows new request with status "Pending"
- ✅ Database: Check `SELECT * FROM supply_requests WHERE id=1;` shows:
  - status = 'Pending'
  - requested_by = staff user ID
  - branch_id = 1
  - created_at = NOW()

---

### Test Case 2: Admin Approves Request

**Actor:** admin user

**Steps:**

1. Login as `admin` / `password123`
2. Navigate to: `/supply-request` (admin dashboard)
3. See card for "Request #1" with "Pending" badge
4. View request details and items in table
5. Click "Approve" button
6. Modal opens asking for approval notes
7. Enter optional note: "Approved - sufficient budget"
8. Click "Approve" button in modal

**Expected Result:**
- ✅ Alert: "Supply request approved! Branch manager has been notified."
- ✅ Page reloads or card disappears from Pending list
- ✅ Database: Check `SELECT * FROM supply_requests WHERE id=1;` shows:
  - status = 'Approved'
  - approved_by = admin user ID
  - approved_at = NOW()
- ✅ **Database: Check `SELECT * FROM notifications WHERE related_id=1;` shows:**
  - recipient_id = manager user ID
  - type = 'supply_request_approved'
  - title = 'Supply Request #1 Approved'
  - message contains: "Supply request #1 from Main Branch has been approved by Central Admin. 2 items requested."
  - is_read = 0 (unread)
  - related_type = 'supply_request'

---

### Test Case 3: Branch Manager Sees Notification

**Actor:** manager user (branch_id = 1)

**Steps:**

1. Login as `manager` / `password123`
2. Navigate to notifications or check API
3. Fetch: `GET /api/notifications?unread=true`
4. Response includes the new notification

**Expected Result:**
- ✅ API returns: `{notifications: [{...}], unreadCount: 1}`
- ✅ Notification object contains:
  - `id`: notification ID
  - `type`: "supply_request_approved"
  - `title`: "Supply Request #1 Approved"
  - `message`: Full approval message
  - `related_id`: 1 (supply request ID)
  - `is_read`: 0
  - `created_at`: timestamp

---

### Test Case 4: Admin Rejects Request

**Steps:**

1. Login as `admin`
2. Go to `/supply-request`
3. Click "Reject" on a pending request
4. Modal opens with "Reason for Rejection" textarea
5. Enter reason: "Insufficient budget allocated for Q1"
6. Click "Reject" button

**Expected Result:**
- ✅ Alert: "Supply request rejected."
- ✅ Page reloads
- ✅ Database: Check `SELECT * FROM supply_requests WHERE id=X;` shows:
  - status = 'Rejected'
  - rejected_reason = "Insufficient budget allocated for Q1"
  - approved_by = admin user ID
  - approved_at = NOW()

---

## Database Verification Queries

### Check all pending requests
```sql
SELECT sr.id, sr.branch_id, u.full_name, sr.total_items, sr.created_at
FROM supply_requests sr
JOIN users u ON sr.requested_by = u.id
WHERE sr.status = 'Pending'
ORDER BY sr.created_at DESC;
```

### Check notifications for a user
```sql
SELECT n.id, n.title, n.message, n.is_read, n.created_at
FROM notifications n
WHERE n.recipient_id = 2  -- manager user ID
ORDER BY n.created_at DESC;
```

### Check activity logs for supply requests
```sql
SELECT al.action, u.full_name, al.details, al.created_at
FROM activity_logs al
JOIN users u ON al.user_id = u.id
WHERE al.action LIKE 'supply_request%'
ORDER BY al.created_at DESC;
```

### Check request with line items
```sql
SELECT sr.id, sr.status, sr.total_items, i.name, sri.quantity_requested
FROM supply_requests sr
LEFT JOIN supply_request_items sri ON sr.id = sri.supply_request_id
LEFT JOIN items i ON sri.item_id = i.id
WHERE sr.id = 1;
```

---

## API Endpoints Reference

### Submit Supply Request
```bash
POST /supply-request/submit
Content-Type: application/json
X-Requested-With: XMLHttpRequest

{
  "items": [
    {"item_id": 1, "quantity": 10, "notes": "Organic"},
    {"item_id": 2, "quantity": 5}
  ],
  "notes": "Low stock"
}

# Response: {success: true, requestId: 123, message: "..."}
```

### Approve Request
```bash
POST /supply-request/approve
Content-Type: application/json
X-Requested-With: XMLHttpRequest

{
  "request_id": 123,
  "approval_notes": "Approved"
}

# Response: {success: true, message: "Branch manager has been notified."}
```

### Reject Request
```bash
POST /supply-request/reject
Content-Type: application/json
X-Requested-With: XMLHttpRequest

{
  "request_id": 123,
  "reason": "Insufficient funds"
}

# Response: {success: true, message: "..."}
```

### Get Pending Count
```bash
GET /supply-request/pending-count

# Response: {count: 3}
```

### Get User's Requests
```bash
GET /supply-request/my-requests
Header: X-Requested-With: XMLHttpRequest

# Response: {requests: [{id: 1, status: "Pending", ...}]}
```

### Get Items (for dropdown)
```bash
GET /api/items

# Response: [{id: 1, name: "Organic Apples", sku: "SKU-APP-001", category: "Produce"}, ...]
```

### Get Notifications
```bash
GET /api/notifications?unread=true&limit=20&offset=0

# Response: {notifications: [...], unreadCount: 2}
```

### Mark Notification as Read
```bash
POST /api/notifications/456/read

# Response: {success: true}
```

---

## Common Issues & Troubleshooting

### Issue: "User has no assigned branch"
- **Cause:** Staff user doesn't have a branch_id
- **Fix:** Check `users` table, ensure staff has `branch_id` set
- **Query:** `SELECT id, username, branch_id FROM users WHERE username='staff';`

### Issue: Notification not created after approval
- **Cause:** Branch manager not found or database transaction failed
- **Fix:** Check:
  1. Is there a user with role "branch_manager" for the branch?
  2. Check logs: `tail writable/logs/log-*.log`
  3. Verify notifications table: `SELECT * FROM notifications;`

### Issue: Admin can't access `/supply-request`
- **Cause:** User doesn't have admin role
- **Fix:** Check user_roles:
  ```sql
  SELECT ur.*, r.name FROM user_roles ur
  JOIN roles r ON ur.role_id = r.id
  WHERE ur.user_id = 1;  -- admin user ID
  ```

### Issue: Items dropdown empty
- **Cause:** No items in database
- **Fix:** Run seeders: `php spark db:seed ItemSeeder`

---

## Files to Know

| File | Purpose |
|------|---------|
| `app/Models/SupplyRequestModel.php` | Core business logic for requests |
| `app/Models/NotificationModel.php` | Notification CRUD |
| `app/Controllers/SupplyRequest.php` | Request submission & approval |
| `app/Controllers/Api/Items.php` | Item list API |
| `app/Controllers/Api/Notifications.php` | Notification API |
| `app/Views/supply_request/admin_dashboard.php` | Admin approve/reject UI |
| `app/Views/supply_request/staff_submit.php` | Staff submission form |
| `app/Config/Routes.php` | All endpoints defined |
| `app/Database/Migrations/2025-01-02-000002_...` | Schema creation |

---

## Performance Considerations

- ✅ Indexed queries: (branch_id, status), (recipient_id, is_read)
- ✅ Transactions ensure data consistency
- ✅ Lazy loading of request items
- ⚠️ For high volume, consider pagination on admin dashboard

---

## Security Notes

- ✅ All endpoints require `auth` filter
- ✅ AJAX requests checked for `X-Requested-With` header
- ✅ Database foreign keys prevent orphaned records
- ✅ Activity logs track all changes
- ⚠️ Ensure approval by central_admin role only (can add role filter if needed)

