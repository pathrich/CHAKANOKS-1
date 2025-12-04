<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['name' => 'central_admin'],
            ['name' => 'branch_manager'],
            ['name' => 'inventory_staff'],
            ['name' => 'supplier'],
            ['name' => 'franchise'],
        ];
        $this->db->table('roles')->emptyTable();
        $this->db->table('roles')->insertBatch($roles);
    }
}


