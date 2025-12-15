<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table            = 'notifications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'recipient_id',
        'type',
        'title',
        'message',
        'related_id',
        'related_type',
        'is_read',
        'read_at',
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = null;

    /**
     * Get unread notifications for a user
     */
    public function getUnread($userId)
    {
        return $this->where('recipient_id', $userId)
                    ->where('is_read', 0)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get all notifications for a user (paginated)
     */
    public function getForUser($userId, $limit = 20, $offset = 0)
    {
        return $this->where('recipient_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit, $offset)
                    ->findAll();
    }

    /**
     * Count unread notifications for a user
     */
    public function countUnread($userId)
    {
        return $this->where('recipient_id', $userId)
                    ->where('is_read', 0)
                    ->countAllResults();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId)
    {
        return $this->update(
            $notificationId,
            [
                'is_read' => 1,
                'read_at' => date('Y-m-d H:i:s'),
            ]
        );
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($userId)
    {
        return $this->where('recipient_id', $userId)
                    ->update(
                        [
                            'is_read' => 1,
                            'read_at' => date('Y-m-d H:i:s'),
                        ]
                    );
    }

    /**
     * Create notification
     */
    public function createNotification($recipientId, $type, $title, $message, $relatedId = null, $relatedType = null)
    {
        $data = [
            'recipient_id' => $recipientId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'related_id' => $relatedId,
            'related_type' => $relatedType,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $builder = $this->db->table($this->table);
        $ok = $builder->insert($data);
        return $ok ? $this->db->insertID() : false;
    }
}
