# Supply Request Management System - Implementation Guide

## Overview
This system manages supply requests submitted by branch staff to the central admin. The workflow is:

1. **Staff submits supply request** → Request logged as "Pending" in database
2. **Admin reviews pending requests** on dashboard
3. **Admin approves request** → Branch manager is automatically notified

---

## Database Schema

### Tables Created

#### `supply_requests`
- `id` - Primary key (BIGINT)
- `branch_id` - Which branch is requesting (FK → branches)
- `requested_by` - User who submitted (FK → users)
- `status` - Enum: Pending, Approved, Rejected, Fulfilled
- `total_items` - Count of requested items
- `notes` - Optional notes from requester
- `approved_by` - Admin who approved (FK → users, nullable)
- `approved_at` - Timestamp of approval
- `rejected_reason` - If rejected, reason why
- `created_at`, `updated_at` - Timestamps

**Indexes:** (branch_id, status), (requested_by)

#### `supply_request_items`
- `id` - Primary key (BIGINT)
- `supply_request_id` - FK → supply_requests
- `item_id` - FK → items
- `quantity_requested` - How many requested
- `quantity_approved` - How many approved (nullable)
- `notes` - Optional item-specific notes

#### `notifications`
- `id` - Primary key (BIGINT)
- `recipient_id` - User receiving notification (FK → users)
- `type` - Notification type (e.g., "supply_request_approved")
- `title` - Short title
- `message` - Full message text
- `related_id` - Reference to supply_request or other entity
- `related_type` - Type of related entity
- `is_read` - Boolean flag
- `created_at`, `read_at` - Timestamps

**Indexes:** (recipient_id, is_read)

---

## Models

### SupplyRequestModel (`app/Models/SupplyRequestModel.php`)

**Key Methods:**

- `getPending()` - Get all pending requests with requester and branch info
- `getByBranch($branchId)` - Get requests for specific branch
- `getWithItems($requestId)` - Get request with full line items
- `createWithItems($branchId, $requestedBy, $items, $notes)` - Create request + items in transaction
  - Logs activity
  - Returns requestId
  
- `approveRequest($requestId, $approvedBy, $approvalNotes)` - Approve + **auto-notify branch manager**
  - Updates status to "Approved"
  - Calls `notifyBranchManager()` internally
  - Logs activity
  - Returns requestId

- `rejectRequest($requestId, $rejectedBy, $reason)` - Reject with reason
  - Updates status to "Rejected"
  - Logs activity

- `notifyBranchManager($request, $approvedBy)` - Private method
  - Gets branch manager for the branch
  - Creates notification record
  - **This is called automatically on approval**

### NotificationModel (`app/Models/NotificationModel.php`)

**Key Methods:**

- `getUnread($userId)` - Get unread notifications for user
- `getForUser($userId, $limit, $offset)` - Paginated notifications
- `countUnread($userId)` - Count unread
- `markAsRead($notificationId, $userId)` - Mark as read with timestamp
- `markAllAsRead($userId)` - Mark all as read
- `createNotification(...)` - Create notification record

---

## Controllers

### SupplyRequest Controller (`app/Controllers/SupplyRequest.php`)

**Routes & Methods:**

| Route | Method | Auth | Purpose |
|-------|--------|------|---------|
| `GET /supply-request` | `index()` | auth | Admin dashboard - view pending requests |
| `POST /supply-request/submit` | `submit()` | auth | Staff submits request (AJAX) |
| `POST /supply-request/approve` | `approve()` | auth | Admin approves (AJAX, triggers notification) |
| `POST /supply-request/reject` | `reject()` | auth | Admin rejects (AJAX) |
| `GET /supply-request/pending-count` | `getPendingCount()` | auth | Get count for badge |
| `GET /supply-request/my-requests` | `myRequests()` | auth | Staff views their requests |

**Key Implementation Details:**

- `submit()`: 
  - Gets user's branch from session
  - Validates items array
  - Calls `createWithItems()` 
  - Returns JSON with requestId

- `approve()`:
  - Gets request_id from JSON POST
  - Calls `approveRequest()` which:
    - Updates DB status
    - **Automatically notifies branch manager via database**
    - Logs activity
  - Returns success JSON

### API Controllers

#### `Api/Items` (`app/Controllers/Api/Items.php`)
- `list()` - Returns JSON array of all items with categories

#### `Api/Notifications` (`app/Controllers/Api/Notifications.php`)
- `list()` - Get notifications (optionally unread only)
- `markRead($notificationId)` - Mark single notification as read

---

## Views

### Admin Dashboard (`app/Views/supply_request/admin_dashboard.php`)
- Shows pending supply requests in card layout
- Displays request items in table
- Approve/Reject buttons with modals
- Modals for approval notes and rejection reasons
- On approval: calls `/supply-request/approve` → branch manager notified automatically
- On rejection: calls `/supply-request/reject` with reason

### Staff Submit Form (`app/Views/supply_request/staff_submit.php`)
- Dynamic item rows (add/remove)
- Item select dropdown (fetches from `/api/items`)
- Quantity input
- Optional notes per item
- Submit button calls `/supply-request/submit`
- Shows previous requests with status badges

---

## Workflow: Request Submission to Approval

### Step 1: Staff Submits Request
```
Staff fills form with:
- Items (id, qty, notes)
- Overall notes
- Clicks "Submit Request"
    ↓
POST /supply-request/submit
    ↓
SupplyRequest::submit()
- Gets staff user's branch_id
- Validates items
- Calls SupplyRequestModel::createWithItems()
    ↓
    Database Transaction:
    - INSERT into supply_requests (branch_id, requested_by, status='Pending', ...)
    - INSERT into supply_request_items (multiple rows)
    - INSERT into activity_logs (action='supply_request_submitted')
    ↓
Returns JSON: {success: true, requestId: 123}
```

### Step 2: Admin Approves Request
```
Admin views pending requests on /supply-request dashboard
- Sees card with request details and line items
- Clicks "Approve" button
- Modal opens for optional approval notes
- Clicks "Approve" in modal
    ↓
POST /supply-request/approve
    ↓
SupplyRequest::approve()
- Gets request_id from JSON
- Calls SupplyRequestModel::approveRequest()
    ↓
    Database Transaction:
    - UPDATE supply_requests SET status='Approved', approved_by=adminId, approved_at=NOW()
    - Calls notifyBranchManager($request, adminId)
        ↓
        (AUTOMATIC BRANCH MANAGER NOTIFICATION)
        - Queries for branch manager user
        - Creates entry in notifications table:
          {
            recipient_id: managerUserId,
            type: 'supply_request_approved',
            title: 'Supply Request #123 Approved',
            message: 'Supply request #123 from [...] branch has been approved...',
            related_id: 123,
            related_type: 'supply_request',
            is_read: 0
          }
    - INSERT into activity_logs (action='supply_request_approved')
    ↓
Returns JSON: {success: true, message: 'Branch manager has been notified'}
```

### Step 3: Branch Manager Sees Notification
- Manager logs into dashboard
- Notification appears (unread)
- Can view via `/api/notifications` endpoint
- Can mark as read

---

## API Usage Examples

### Staff Submitting Request
```javascript
fetch('/supply-request/submit', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({
        items: [
            { item_id: 1, quantity: 10, notes: 'Organic' },
            { item_id: 2, quantity: 5, notes: null }
        ],
        notes: 'Urgent - low stock'
    })
})
.then(r => r.json())
.then(data => {
    // data: {success: true, requestId: 123, message: '...'}
});
```

### Admin Getting Items for Dropdown
```javascript
fetch('/api/items')
    .then(r => r.json())
    .then(items => {
        // items: [{id: 1, name: 'Organic Apples', sku: 'SKU-APP-001', ...}]
    });
```

### Getting Notifications
```javascript
fetch('/api/notifications?unread=true')
    .then(r => r.json())
    .then(data => {
        // data: {
        //   notifications: [...],
        //   unreadCount: 3
        // }
    });
```

### Marking Notification as Read
```javascript
fetch('/api/notifications/456/read', { method: 'POST' })
    .then(r => r.json())
    .then(data => {
        // data: {success: true}
    });
```

---

## Key Features

✅ **Automatic Logging**
- All supply requests are logged in `activity_logs` table
- Staff, admin, and branch manager actions tracked

✅ **Automatic Notification on Approval**
- When admin clicks "Approve", notification is **automatically created** in database
- Branch manager will see it when they check notifications
- Notification includes request ID and summary

✅ **Transaction Safety**
- Supply request creation wrapped in DB transaction
- Approval process wrapped in transaction
- If anything fails, rollback occurs

✅ **Proper Foreign Keys**
- All relationships enforced at database level
- CASCADE deletes for data integrity

✅ **AJAX Integration**
- All submits/approvals asynchronous
- JSON responses for frontend handling
- X-Requested-With header validation

✅ **Role-Based Access**
- Staff can only see their branch's requests
- Admin can see all pending requests
- Notifications sent to specific users (branch managers)

---

## Running the System

### 1. Run Migrations
```bash
php spark migrate
```

### 2. Test Staff Submission
```
POST /supply-request/submit
Content-Type: application/json
X-Requested-With: XMLHttpRequest

{
  "items": [
    {"item_id": 1, "quantity": 10}
  ],
  "notes": "Low stock"
}
```

### 3. Test Admin Approval
```
POST /supply-request/approve
Content-Type: application/json
X-Requested-With: XMLHttpRequest

{
  "request_id": 1,
  "approval_notes": "Approved"
}
```
→ **Branch manager will be automatically notified via notifications table**

### 4. Verify Notification Created
```sql
SELECT * FROM notifications WHERE recipient_id = [branch_manager_id];
```

---

## Files Created

- ✅ Migration: `app/Database/Migrations/2025-01-02-000002_CreateSupplyRequestSchema.php`
- ✅ Model: `app/Models/SupplyRequestModel.php`
- ✅ Model: `app/Models/NotificationModel.php`
- ✅ Controller: `app/Controllers/SupplyRequest.php`
- ✅ API Controller: `app/Controllers/Api/Items.php`
- ✅ API Controller: `app/Controllers/Api/Notifications.php`
- ✅ View: `app/Views/supply_request/admin_dashboard.php`
- ✅ View: `app/Views/supply_request/staff_submit.php`
- ✅ Routes: Updated `app/Config/Routes.php`

---

## Database Diagram

```
users
  ├─ id (PK)
  ├─ full_name
  ├─ branch_id (FK → branches)
  └─ role_id (FK → roles via user_roles)

branches
  ├─ id (PK)
  └─ name

supply_requests
  ├─ id (PK)
  ├─ branch_id (FK → branches)
  ├─ requested_by (FK → users) [staff member]
  ├─ approved_by (FK → users) [admin]
  ├─ status (Pending/Approved/Rejected/Fulfilled)
  ├─ created_at

supply_request_items
  ├─ id (PK)
  ├─ supply_request_id (FK)
  ├─ item_id (FK → items)
  └─ quantity_requested

notifications
  ├─ id (PK)
  ├─ recipient_id (FK → users) [branch manager]
  ├─ type (supply_request_approved)
  ├─ title
  ├─ message
  ├─ related_id (points to supply_requests.id)
  ├─ is_read
  └─ created_at

activity_logs
  ├─ id (PK)
  ├─ user_id (FK → users)
  ├─ action (supply_request_submitted/approved/rejected)
  └─ created_at
```

---

## Extensibility

Future enhancements could include:

- [ ] Email notifications in addition to in-app
- [ ] SMS alerts for urgent approvals
- [ ] Approval workflow with multiple stages
- [ ] Budget checking before approval
- [ ] Supplier integration (auto-create PO)
- [ ] Request modification before approval
- [ ] Approval history/audit trail with notes
- [ ] Dashboard metrics (approval rate, avg time to approve)

