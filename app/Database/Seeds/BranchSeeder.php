<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class BranchSeeder extends Seeder
{
    public function run()
    {
        $now = Time::now('UTC');
        $branches = [
            [ 'name' => 'Main Branch', 'code' => 'DAV-MAIN', 'city' => 'Davao City', 'created_at' => $now, 'updated_at' => $now ],
            [ 'name' => 'North Branch', 'code' => 'DAV-NORTH', 'city' => 'Davao City', 'created_at' => $now, 'updated_at' => $now ],
            [ 'name' => 'South Branch', 'code' => 'DAV-SOUTH', 'city' => 'Davao City', 'created_at' => $now, 'updated_at' => $now ],
            [ 'name' => 'East Branch', 'code' => 'DAV-EAST', 'city' => 'Davao City', 'created_at' => $now, 'updated_at' => $now ],
            [ 'name' => 'West Branch', 'code' => 'DAV-WEST', 'city' => 'Davao City', 'created_at' => $now, 'updated_at' => $now ],
        ];
        db_connect()->table('branches')->ignore(true)->insertBatch($branches);
    }
}


