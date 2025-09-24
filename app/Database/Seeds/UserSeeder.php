<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class UserSeeder extends Seeder
{
    public function run()
    {
        $now = Time::now('UTC');
        $password = password_hash('password123', PASSWORD_BCRYPT);
        $users = [
            [ 'username' => 'manager', 'password_hash' => $password, 'full_name' => 'Branch Manager', 'branch_id' => 1, 'created_at' => $now, 'updated_at' => $now ],
            [ 'username' => 'staff', 'password_hash' => $password, 'full_name' => 'Inventory Staff', 'branch_id' => 1, 'created_at' => $now, 'updated_at' => $now ],
            [ 'username' => 'admin', 'password_hash' => $password, 'full_name' => 'Central Admin', 'branch_id' => null, 'created_at' => $now, 'updated_at' => $now ],
        ];
        db_connect()->table('users')->ignore(true)->insertBatch($users);
    }
}


