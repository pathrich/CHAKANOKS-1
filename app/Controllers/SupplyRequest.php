<?php

namespace App\Controllers;

use App\Models\SupplyRequestModel;
use App\Models\NotificationModel;

class SupplyRequest extends BaseController
{
    protected $supplyRequestModel;
    protected $notificationModel;

    public function __construct()
    {
        $this->supplyRequestModel = new SupplyRequestModel();
        $this->notificationModel = new NotificationModel();
    }

    /**
     * Display supply requests dashboard (admin view)
     */
    public function index()
    {
        // Ensure user is authenticated
        if (!session('user_id')) {
            return redirect()->to(site_url('login'));
        }

        try {
            // Check if admin
            $db = db_connect();
            $userRoles = $db->table('roles')->select('roles.name')
                            ->join('user_roles', 'user_roles.role_id = roles.id')
                            ->where('user_roles.user_id', session('user_id'))
                            ->get()->getResultArray();
            $roleNames = array_map(function ($r) {
                return $r['name'];
            }, $userRoles);
            $isAdmin = in_array('central_admin', $roleNames, true) || in_array('system_admin', $roleNames, true);

            if (!$isAdmin) {
                return $this->response->setStatusCode(403)->setBody('Access denied');
            }

            // Get pending requests
            $pendingRequests = $this->supplyRequestModel->getPending();

            // Get request details with items for each
            foreach ($pendingRequests as &$request) {
                $request = $this->supplyRequestModel->getWithItems($request['id']);
            }

            $data = [
                'pendingRequests' => $pendingRequests,
                'title' => 'Supply Requests',
            ];

            return view('supply_request/admin_dashboard', $data);
        } catch (\Throwable $e) {
            log_message('error', 'SupplyRequest index failed: ' . $e->getMessage());
            return $this->response
                ->setStatusCode(500)
                ->setBody('Failed to load supply requests: ' . $e->getMessage());
        }
    }

    /**
     * Admin: fetch all supply requests (any status) for the "All Requests" tab
     * GET: /supply-request/all
     */
    public function allRequests()
    {
        if (!session('user_id')) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'error' => 'Not authenticated']);
        }

        $db = db_connect();
        $userRoles = $db->table('roles')->select('roles.name')
            ->join('user_roles', 'user_roles.role_id = roles.id')
            ->where('user_roles.user_id', session('user_id'))
            ->get()->getResultArray();
        $roleNames = array_map(function ($r) {
            return $r['name'];
        }, $userRoles);
        $isAdmin = in_array('central_admin', $roleNames, true) || in_array('system_admin', $roleNames, true);

        if (!$isAdmin) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'error' => 'Access denied']);
        }

        try {
            $requests = $this->supplyRequestModel
                ->select('supply_requests.*, users.full_name as requester_name, branches.name as branch_name')
                ->join('users', 'users.id = supply_requests.requested_by')
                ->join('branches', 'branches.id = supply_requests.branch_id')
                ->orderBy('supply_requests.created_at', 'DESC')
                ->findAll();

            foreach ($requests as &$r) {
                $withItems = $this->supplyRequestModel->getWithItems($r['id']);
                if ($withItems) {
                    $r['items'] = $withItems['items'] ?? [];
                } else {
                    $r['items'] = [];
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'requests' => $requests,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to load all supply requests: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'error' => 'Failed to load requests']);
        }
    }

    public function create()
    {
        if (!session('user_id')) {
            return redirect()->to(site_url('login'));
        }

        $db = db_connect();
        $suppliers = [];
        if ($db->tableExists('suppliers')) {
            $suppliers = $db->table('suppliers')->select('id, name')->orderBy('name', 'ASC')->get()->getResultArray();
        }

        $data = [
            'title' => 'Submit Supply Request',
            'suppliers' => $suppliers,
        ];

        return view('supply_request/staff_submit', $data);
    }

    /**
     * Staff submits a supply request for their branch
     * Expected POST data:
     * - items: array of ['item_id' => int, 'quantity' => int, 'notes' => string (optional)]
     * - notes: string (optional)
     */
    public function submit()
    {
        try {
            if (!$this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX request required']);
            }

            if (!session('user_id')) {
                return $this->response->setStatusCode(401)->setJSON(['error' => 'Not authenticated']);
            }

            $db = db_connect();

            // Get user's branch
            $user = $db->table('users')
                       ->select('branch_id')
                       ->where('id', session('user_id'))
                       ->get()
                       ->getRowArray();

            $branchId = $user['branch_id'] ?? null;

            // If user has no branch or an invalid branch, automatically attach to first existing branch
            $branch = null;
            if ($branchId) {
                $branch = $db->table('branches')
                             ->select('id')
                             ->where('id', $branchId)
                             ->get()
                             ->getRowArray();
            }

            if (! $branch) {
                // Pick the first branch as a default
                $defaultBranch = $db->table('branches')
                                    ->select('id')
                                    ->orderBy('id', 'ASC')
                                    ->get()
                                    ->getRowArray();

                if (! $defaultBranch) {
                    return $this->response->setStatusCode(500)->setJSON([
                        'error' => 'No branches are configured in the system. Please ask an admin to add a branch first.',
                    ]);
                }

                $branchId = (int) $defaultBranch['id'];

                // Persist this assignment back to the user so future requests are consistent
                $db->table('users')
                   ->where('id', session('user_id'))
                   ->update(['branch_id' => $branchId]);

                // Optionally log this change
                $db->table('activity_logs')->insert([
                    'user_id'    => session('user_id'),
                    'action'     => 'user_branch_auto_assigned',
                    'details'    => json_encode(['branch_id' => $branchId]),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            // Validate input
            $json = $this->request->getJSON();
            $items = $json->items ?? [];
            $notes = $json->notes ?? null;
            $preferredSupplierId = isset($json->preferred_supplier_id) ? (int) $json->preferred_supplier_id : null;
            $requiredDeliveryDate = isset($json->required_delivery_date) ? (string) $json->required_delivery_date : null;

            if (empty($items)) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'No items provided']);
            }

            // Create supply request for the resolved branch
            $requestId = $this->supplyRequestModel->createWithItems(
                $branchId,
                session('user_id'),
                $items,
                $notes
            );

            if ($requestId) {
                $db->table('supply_requests')->where('id', (int) $requestId)->update([
                    'preferred_supplier_id' => $preferredSupplierId ?: null,
                    'required_delivery_date' => $requiredDeliveryDate ?: null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                $roleRows = $db->table('roles')
                    ->select('roles.name')
                    ->join('user_roles', 'user_roles.role_id = roles.id')
                    ->where('user_roles.user_id', (int) session('user_id'))
                    ->get()->getResultArray();
                $roleNames = array_map(function ($r) {
                    return $r['name'];
                }, $roleRows);

                if (in_array('branch_manager', $roleNames, true)) {
                    $this->supplyRequestModel->branchApproveRequest((int) $requestId, (int) session('user_id'));
                    $this->notifyCentralAdmins(
                        'Supply Request Pending Central Approval',
                        'A supply request has been submitted by a branch manager and is awaiting central approval.',
                        (int) $requestId
                    );
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'requestId' => $requestId,
                'message' => 'Supply request submitted successfully',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Supply request submission failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Failed to submit request: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Admin approves a supply request
     * Expected POST data:
     * - request_id: int
     * - approval_notes: string (optional)
     */
    public function approve()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX request required']);
        }

        if (!session('user_id')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not authenticated']);
        }

        $json = $this->request->getJSON(true);
        $requestId = (int) ($json['request_id'] ?? 0);
        $approvalNotes = $json['approval_notes'] ?? null;

        if (!$requestId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request ID']);
        }

        try {
            // Approve request (this will also notify branch manager)
            $this->supplyRequestModel->approveRequest($requestId, session('user_id'), $approvalNotes);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Supply request approved. Branch manager has been notified.',
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Supply request approval failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to approve request']);
        }
    }

    /**
     * Admin rejects a supply request
     * Expected POST data:
     * - request_id: int
     * - reason: string
     */
    public function reject()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX request required']);
        }

        if (!session('user_id')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not authenticated']);
        }

        $json = $this->request->getJSON(true);
        $requestId = (int) ($json['request_id'] ?? 0);
        $reason = (string) ($json['reason'] ?? '');

        if (!$requestId || !$reason) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request or reason']);
        }

        try {
            $this->supplyRequestModel->rejectRequest($requestId, session('user_id'), $reason);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Supply request rejected.',
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Supply request rejection failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to reject request']);
        }
    }

    /**
     * Get pending requests count (for admin dashboard badge)
     */
    public function getPendingCount()
    {
        if (!session('user_id')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not authenticated']);
        }

        try {
            $count = $this->supplyRequestModel
                          ->where('supply_requests.status', 'Pending Central Approval')
                          ->countAllResults();

            return $this->response->setJSON(['count' => $count]);
        } catch (\Throwable $e) {
            log_message('error', 'SupplyRequest getPendingCount failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to get pending count']);
        }
    }

    /**
     * Get supply requests for staff view (their branch)
     */
    public function myRequests()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX request required']);
        }

        if (!session('user_id')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not authenticated']);
        }

        $db = db_connect();

        // Get user's branch (same logic as submit(): auto-assign a valid branch if needed)
        $user = $db->table('users')
                   ->select('branch_id')
                   ->where('id', session('user_id'))
                   ->get()
                   ->getRowArray();

        $branchId = $user['branch_id'] ?? null;

        $branch = null;
        if ($branchId) {
            $branch = $db->table('branches')
                         ->select('id')
                         ->where('id', $branchId)
                         ->get()
                         ->getRowArray();
        }

        if (! $branch) {
            $defaultBranch = $db->table('branches')
                                ->select('id')
                                ->orderBy('id', 'ASC')
                                ->get()
                                ->getRowArray();

            if (! $defaultBranch) {
                return $this->response->setStatusCode(500)->setJSON([
                    'error' => 'No branches are configured in the system.',
                ]);
            }

            $branchId = (int) $defaultBranch['id'];

            // Persist auto-assignment (if not already set)
            $db->table('users')
               ->where('id', session('user_id'))
               ->update(['branch_id' => $branchId]);

            $db->table('activity_logs')->insert([
                'user_id'    => session('user_id'),
                'action'     => 'user_branch_auto_assigned',
                'details'    => json_encode(['branch_id' => $branchId, 'source' => 'myRequests']),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $requests = $this->supplyRequestModel->getByBranch($branchId);

        return $this->response->setJSON(['requests' => $requests]);
    }

    public function pendingBranch()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX request required']);
        }

        if (!session('user_id')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not authenticated']);
        }

        $db = db_connect();
        $user = $db->table('users')->select('branch_id')->where('id', session('user_id'))->get()->getRowArray();
        $branchId = (int) ($user['branch_id'] ?? 0);
        if (!$branchId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'User has no assigned branch']);
        }

        $pending = $this->supplyRequestModel->getPendingBranchApproval($branchId);
        foreach ($pending as &$req) {
            $with = $this->supplyRequestModel->getWithItems($req['id']);
            if ($with) {
                $req = $with;
            }
        }

        return $this->response->setJSON(['success' => true, 'requests' => $pending]);
    }

    public function branchApprove()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX request required']);
        }

        if (!session('user_id')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not authenticated']);
        }

        $json = $this->request->getJSON();
        $requestId = (int) ($json->request_id ?? 0);
        $notes = $json->notes ?? null;
        if (!$requestId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request ID']);
        }

        try {
            $db = db_connect();
            $user = $db->table('users')->select('branch_id')->where('id', session('user_id'))->get()->getRowArray();
            $branchId = (int) ($user['branch_id'] ?? 0);

            $req = $this->supplyRequestModel->find($requestId);
            if (!$req || (int) ($req['branch_id'] ?? 0) !== $branchId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'Not allowed']);
            }

            $this->supplyRequestModel->branchApproveRequest($requestId, (int) session('user_id'), is_string($notes) ? $notes : null);

            $this->notifyCentralAdmins(
                'Supply Request Pending Central Approval',
                'A supply request has been approved by the branch and is awaiting central approval.',
                $requestId
            );

            return $this->response->setJSON(['success' => true]);
        } catch (\Exception $e) {
            log_message('error', 'Branch approve supply request failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    public function branchReject()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX request required']);
        }

        if (!session('user_id')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not authenticated']);
        }

        $json = $this->request->getJSON();
        $requestId = (int) ($json->request_id ?? 0);
        $reason = trim((string) ($json->reason ?? ''));
        if (!$requestId || !$reason) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request or reason']);
        }

        try {
            $db = db_connect();
            $user = $db->table('users')->select('branch_id')->where('id', session('user_id'))->get()->getRowArray();
            $branchId = (int) ($user['branch_id'] ?? 0);

            $req = $this->supplyRequestModel->find($requestId);
            if (!$req || (int) ($req['branch_id'] ?? 0) !== $branchId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'Not allowed']);
            }

            $this->supplyRequestModel->branchRejectRequest($requestId, (int) session('user_id'), $reason);

            $this->notificationModel->createNotification(
                (int) ($req['requested_by'] ?? 0),
                'supply_request_rejected_by_branch',
                'Supply Request Rejected by Branch',
                'Your supply request was rejected by the branch manager: ' . $reason,
                $requestId,
                'supply_request'
            );

            return $this->response->setJSON(['success' => true]);
        } catch (\Exception $e) {
            log_message('error', 'Branch reject supply request failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    private function notifyCentralAdmins(string $title, string $message, ?int $relatedId = null): void
    {
        $db = db_connect();
        if (! $db->tableExists('user_roles') || ! $db->tableExists('roles')) {
            return;
        }

        $adminUserIds = $db->table('user_roles ur')
            ->select('ur.user_id')
            ->join('roles r', 'r.id = ur.role_id')
            ->whereIn('r.name', ['central_admin', 'system_admin'])
            ->get()->getResultArray();

        foreach ($adminUserIds as $row) {
            $uid = (int) ($row['user_id'] ?? 0);
            if ($uid <= 0) {
                continue;
            }
            $this->notificationModel->createNotification(
                $uid,
                'supply_request_pending_central',
                $title,
                $message,
                $relatedId,
                'supply_request'
            );
        }
    }
}
