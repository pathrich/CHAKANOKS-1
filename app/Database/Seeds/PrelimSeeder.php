<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class PrelimSeeder extends Seeder
{
    public function run()
    {
        $this->db->disableForeignKeyChecks();
        try {
            // Call granular seeders using class references
            $this->call(BranchSeeder::class);
            $this->call(RoleSeeder::class);
            $this->call(UserSeeder::class);
            $this->call(UserRoleSeeder::class);
            $this->call(SupplierSeeder::class);
            $this->call(UserSupplierSeeder::class);
            $this->call(CategorySeeder::class);
            $this->call(ItemSeeder::class);
            $this->call(StockSeeder::class);
        } finally {
            $this->db->enableForeignKeyChecks();
        }
    }
}


