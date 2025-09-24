<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class PrelimSeeder extends Seeder
{
    public function run()
    {
        // Call granular seeders
        $this->call('App\Database\Seeds\BranchSeeder');
        $this->call('App\Database\Seeds\RoleSeeder');
        $this->call('App\Database\Seeds\UserSeeder');
        $this->call('App\Database\Seeds\UserRoleSeeder');
        $this->call('App\Database\Seeds\CategorySeeder');
        $this->call('App\Database\Seeds\ItemSeeder');
        $this->call('App\Database\Seeds\StockSeeder');
    }
}


