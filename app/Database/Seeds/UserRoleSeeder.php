<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    public function run()
    {
        $db = db_connect();
        $db->table('user_roles')->emptyTable();
        
        // Resolve IDs by names to avoid mismatch
        $roleMap = [];
        foreach ($db->table('roles')->select('id,name')->get()->getResultArray() as $r) {
            $roleMap[$r['name']] = (int)$r['id'];
        }
        $userMap = [];
        foreach ($db->table('users')->select('id,username')->get()->getResultArray() as $u) {
            $userMap[$u['username']] = (int)$u['id'];
        }

        $pairs = [];
        if (isset($userMap['admin'], $roleMap['central_admin'])) {
            $pairs[] = [ 'user_id' => $userMap['admin'], 'role_id' => $roleMap['central_admin'] ];
        }
        if (isset($userMap['manager'], $roleMap['branch_manager'])) {
            $pairs[] = [ 'user_id' => $userMap['manager'], 'role_id' => $roleMap['branch_manager'] ];
        }
        if (isset($userMap['staff'], $roleMap['inventory_staff'])) {
            $pairs[] = [ 'user_id' => $userMap['staff'], 'role_id' => $roleMap['inventory_staff'] ];
        }
        if (isset($userMap['supplier'], $roleMap['supplier'])) {
            $pairs[] = [ 'user_id' => $userMap['supplier'], 'role_id' => $roleMap['supplier'] ];
        }
        if (isset($userMap['franchise'], $roleMap['franchise'])) {
            $pairs[] = [ 'user_id' => $userMap['franchise'], 'role_id' => $roleMap['franchise'] ];
        }
        if (isset($userMap['logistics'], $roleMap['logistics_coordinator'])) {
            $pairs[] = [ 'user_id' => $userMap['logistics'], 'role_id' => $roleMap['logistics_coordinator'] ];
        }
        if ($pairs) {
            $db->table('user_roles')->insertBatch($pairs);
        }
    }
}


