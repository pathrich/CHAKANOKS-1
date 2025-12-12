<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class StockSeeder extends Seeder
{
    public function run()
    {
        $now = Time::now('UTC')->toDateTimeString();

        $branch = null;
        try {
            if ($this->db->tableExists('branches')) {
                $branch = $this->db->table('branches')->select('id')->orderBy('id', 'ASC')->get()->getRowArray();
            }
        } catch (\Throwable $e) {
            $branch = null;
        }

        if (!$branch) {
            return;
        }

        $branchId = (int) $branch['id'];

        $skus = ['SKU-APP-001', 'SKU-BEF-001', 'SKU-MLK-001'];
        $rows = $this->db->table('items')->select('id,sku')->whereIn('sku', $skus)->get()->getResultArray();
        $itemIdBySku = [];
        foreach ($rows as $r) {
            $itemIdBySku[$r['sku']] = (int) $r['id'];
        }

        $stocks = [];
        if (isset($itemIdBySku['SKU-APP-001'])) {
            $stocks[] = [ 'branch_id' => $branchId, 'item_id' => $itemIdBySku['SKU-APP-001'], 'quantity' => 40, 'expiry_date' => date('Y-m-d', strtotime('+5 days')), 'created_at' => $now, 'updated_at' => $now ];
        }
        if (isset($itemIdBySku['SKU-BEF-001'])) {
            $stocks[] = [ 'branch_id' => $branchId, 'item_id' => $itemIdBySku['SKU-BEF-001'], 'quantity' => 25, 'expiry_date' => date('Y-m-d', strtotime('+10 days')), 'created_at' => $now, 'updated_at' => $now ];
        }
        if (isset($itemIdBySku['SKU-MLK-001'])) {
            $stocks[] = [ 'branch_id' => $branchId, 'item_id' => $itemIdBySku['SKU-MLK-001'], 'quantity' => 15, 'expiry_date' => date('Y-m-d', strtotime('+3 days')), 'created_at' => $now, 'updated_at' => $now ];
        }
        $this->db->table('branch_stocks')->emptyTable();
        if (!empty($stocks)) {
            $this->db->table('branch_stocks')->insertBatch($stocks);
        }
    }
}


