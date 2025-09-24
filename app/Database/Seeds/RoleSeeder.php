<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [ ['name' => 'branch_manager'], ['name' => 'inventory_staff'], ['name' => 'central_admin'] ];
        db_connect()->table('roles')->ignore(true)->insertBatch($roles);
    }
}


