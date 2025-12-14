<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class UserSeeder extends Seeder
{
    public function run()
    {
        $now = Time::now('UTC')->toDateTimeString();
        $password = password_hash('password123', PASSWORD_BCRYPT);
        $users = [
            [ 'username' => 'admin', 'password_hash' => $password, 'full_name' => 'Central Admin', 'branch_id' => null, 'created_at' => $now, 'updated_at' => $now ],
            [ 'username' => 'sysadmin', 'password_hash' => $password, 'full_name' => 'System Administrator', 'branch_id' => null, 'created_at' => $now, 'updated_at' => $now ],
            [ 'username' => 'manager', 'password_hash' => $password, 'full_name' => 'Branch Manager', 'branch_id' => null, 'created_at' => $now, 'updated_at' => $now ],
            [ 'username' => 'staff', 'password_hash' => $password, 'full_name' => 'Inventory Staff', 'branch_id' => null, 'created_at' => $now, 'updated_at' => $now ],
            [ 'username' => 'supplier', 'password_hash' => $password, 'full_name' => 'Supplier User', 'branch_id' => null, 'created_at' => $now, 'updated_at' => $now ],
            [ 'username' => 'franchise', 'password_hash' => $password, 'full_name' => 'Franchise User', 'branch_id' => null, 'created_at' => $now, 'updated_at' => $now ],
            [ 'username' => 'logistics', 'password_hash' => $password, 'full_name' => 'Logistics Coordinator', 'branch_id' => null, 'created_at' => $now, 'updated_at' => $now ],
        ];
        $this->db->table('users')->emptyTable();
        $this->db->table('users')->insertBatch($users);
    }
}
