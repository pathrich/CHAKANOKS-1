<?php

namespace App\Controllers\Api;

use App\Models\NotificationModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;

class Notifications extends Controller
{
    use ResponseTrait;

    protected $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
    }

    /**
     * Get notifications for current user
     */
    public function list()
    {
        if (!session('user_id')) {
            return $this->failUnauthorized('Not authenticated');
        }

        $unreadOnly = $this->request->getGet('unread') === 'true';
        $limit = (int)($this->request->getGet('limit') ?? 20);
        $offset = (int)($this->request->getGet('offset') ?? 0);

        if ($unreadOnly) {
            $notifications = $this->notificationModel->getUnread(session('user_id'));
        } else {
            $notifications = $this->notificationModel->getForUser(session('user_id'), $limit, $offset);
        }

        return $this->respond([
            'notifications' => $notifications,
            'unreadCount' => $this->notificationModel->countUnread(session('user_id')),
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markRead($notificationId)
    {
        if (!session('user_id')) {
            return $this->failUnauthorized('Not authenticated');
        }

        $db = db_connect();

        // Verify notification belongs to user
        $notification = $db->table('notifications')
                           ->where('id', $notificationId)
                           ->where('recipient_id', session('user_id'))
                           ->first();

        if (!$notification) {
            return $this->failNotFound('Notification not found');
        }

        $this->notificationModel->markAsRead($notificationId, session('user_id'));

        return $this->respond(['success' => true]);
    }
}
