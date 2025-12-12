<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $now = gmdate('Y-m-d H:i:s');
        $password = password_hash('password123', PASSWORD_BCRYPT);

        if (! $this->db->tableExists('users')) {
            throw new \RuntimeException('Users table not found. Please run `php spark migrate` first.');
        }

        $branchId = null;
        try {
            if ($this->db->tableExists('branches')) {
                $firstBranch = $this->db->table('branches')->select('id')->orderBy('id', 'ASC')->get()->getRowArray();
                $branchId = $firstBranch ? (int) $firstBranch['id'] : null;
            }
        } catch (\Throwable $e) {
            $branchId = null;
        }

        $users = [
            [ 'username' => 'admin', 'password_hash' => $password, 'full_name' => 'Central Admin', 'branch_id' => null, 'created_at' => $now, 'updated_at' => $now ],
            [ 'username' => 'sysadmin', 'password_hash' => $password, 'full_name' => 'System Administrator', 'branch_id' => null, 'created_at' => $now, 'updated_at' => $now ],
            [ 'username' => 'manager', 'password_hash' => $password, 'full_name' => 'Branch Manager', 'branch_id' => $branchId, 'created_at' => $now, 'updated_at' => $now ],
            [ 'username' => 'staff', 'password_hash' => $password, 'full_name' => 'Inventory Staff', 'branch_id' => $branchId, 'created_at' => $now, 'updated_at' => $now ],
            [ 'username' => 'supplier', 'password_hash' => $password, 'full_name' => 'Supplier User', 'branch_id' => null, 'created_at' => $now, 'updated_at' => $now ],
            [ 'username' => 'franchise', 'password_hash' => $password, 'full_name' => 'Franchise User', 'branch_id' => $branchId, 'created_at' => $now, 'updated_at' => $now ],
            [ 'username' => 'logistics', 'password_hash' => $password, 'full_name' => 'Logistics Coordinator', 'branch_id' => $branchId, 'created_at' => $now, 'updated_at' => $now ],
        ];

        $this->db->disableForeignKeyChecks();
        try {
            // If user_roles exists, clear it first to avoid FK constraint errors
            try {
                if ($this->db->tableExists('user_roles')) {
                    $this->db->table('user_roles')->emptyTable();
                }
            } catch (\Throwable $e) {
                // ignore
            }

            $this->db->table('users')->emptyTable();
            $this->db->table('users')->insertBatch($users);
        } finally {
            $this->db->enableForeignKeyChecks();
        }
    }
}


