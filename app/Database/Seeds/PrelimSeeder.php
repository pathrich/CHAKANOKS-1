<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class PrelimSeeder extends Seeder
{
    public function run()
    {
        // Call granular seeders using class references
        $this->call(BranchSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(UserRoleSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(ItemSeeder::class);
        $this->call(StockSeeder::class);
    }
}


