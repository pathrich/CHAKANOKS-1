<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class StockSeeder extends Seeder
{
    public function run()
    {
        $now = Time::now('UTC')->toDateTimeString();
        $stocks = [
            [ 'branch_id' => 1, 'item_id' => 1, 'quantity' => 40, 'expiry_date' => date('Y-m-d', strtotime('+5 days')), 'created_at' => $now, 'updated_at' => $now ],
            [ 'branch_id' => 1, 'item_id' => 2, 'quantity' => 25, 'expiry_date' => date('Y-m-d', strtotime('+10 days')), 'created_at' => $now, 'updated_at' => $now ],
            [ 'branch_id' => 1, 'item_id' => 3, 'quantity' => 15, 'expiry_date' => date('Y-m-d', strtotime('+3 days')), 'created_at' => $now, 'updated_at' => $now ],
        ];
        $this->db->table('branch_stocks')->emptyTable();
        $this->db->table('branch_stocks')->insertBatch($stocks);
    }
}


