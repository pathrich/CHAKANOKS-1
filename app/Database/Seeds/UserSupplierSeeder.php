<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSupplierSeeder extends Seeder
{
    public function run()
    {
        $now = gmdate('Y-m-d H:i:s');

        if (! $this->db->tableExists('user_suppliers')) {
            throw new \RuntimeException('user_suppliers table not found. Please run migrations first.');
        }

        $db = db_connect();

        $user = $db->table('users')->select('id')->where('username', 'supplier')->get()->getRowArray();
        if (! $user) {
            return;
        }

        $supplier = $db->table('suppliers')->select('id')->orderBy('id', 'ASC')->get()->getRowArray();
        if (! $supplier) {
            return;
        }

        // idempotent insert
        $exists = $db->table('user_suppliers')
            ->where('user_id', (int) $user['id'])
            ->where('supplier_id', (int) $supplier['id'])
            ->countAllResults();

        if ($exists === 0) {
            $db->table('user_suppliers')->insert([
                'user_id' => (int) $user['id'],
                'supplier_id' => (int) $supplier['id'],
                'created_at' => $now,
            ]);
        }
    }
}
