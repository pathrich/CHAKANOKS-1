<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table = 'audit_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id','role','action','details','created_at'];
    protected $useTimestamps = false;

    public function log($action, $details = null, $userId = null, $role = null)
    {
        $this->insert([
            'user_id' => $userId,
            'role' => $role,
            'action' => $action,
            'details' => $details,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
