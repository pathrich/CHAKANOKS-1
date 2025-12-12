<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run()
    {
        $now = gmdate('Y-m-d H:i:s');

        if (! $this->db->tableExists('suppliers')) {
            throw new \RuntimeException('Suppliers table not found. Please run migrations first.');
        }

        $exists = $this->db->table('suppliers')->where('name', 'Default Supplier')->countAllResults();
        if ($exists === 0) {
            $this->db->table('suppliers')->insert([
                'name' => 'Default Supplier',
                'contact_name' => 'Supplier Contact',
                'contact_email' => 'supplier@example.com',
                'contact_phone' => '0000000000',
                'address' => 'N/A',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
