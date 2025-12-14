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

        // Check if admin
        $db = db_connect();
        $userRoles = $db->table('roles')->select('roles.name')
                        ->join('user_roles', 'user_roles.role_id = roles.id')
                        ->where('user_roles.user_id', session('user_id'))
                        ->get()->getResultArray();
        $roleNames = array_map(fn($r) => $r['name'], $userRoles);
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
        $roleNames = array_map(fn($r) => $r['name'], $userRoles);
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

        $data = [
            'title' => 'Submit Supply Request',
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

        if (!$user || !$user['branch_id']) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'User has no assigned branch']);
        }

        // Validate input
        $items = $this->request->getJSON()->items ?? [];
        $notes = $this->request->getJSON()->notes ?? null;

        if (empty($items)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'No items provided']);
        }

        try {
            // Create supply request
            $requestId = $this->supplyRequestModel->createWithItems(
                $user['branch_id'],
                session('user_id'),
                $items,
                $notes
            );

            return $this->response->setJSON([
                'success' => true,
                'requestId' => $requestId,
                'message' => 'Supply request submitted successfully',
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Supply request submission failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to submit request']);
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

        $requestId = (int)$this->request->getJSON()->request_id ?? 0;
        $approvalNotes = $this->request->getJSON()->approval_notes ?? null;

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

        $requestId = (int)$this->request->getJSON()->request_id ?? 0;
        $reason = $this->request->getJSON()->reason ?? '';

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

        $count = $this->supplyRequestModel
                      ->where('status', 'Pending')
                      ->countAllResults();

        return $this->response->setJSON(['count' => $count]);
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

        // Get user's branch
        $user = $db->table('users')
                   ->select('branch_id')
                   ->where('id', session('user_id'))
                   ->get()
                   ->getRowArray();

        if (!$user || !$user['branch_id']) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'User has no assigned branch']);
        }

        $requests = $this->supplyRequestModel->getByBranch($user['branch_id']);

        return $this->response->setJSON(['requests' => $requests]);
    }
}
