<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run()
    {
        $items = [
            [ 'name' => 'Whole chicken', 'sku' => 'SKU-CHK-WHOLE', 'barcode' => '1234567890123', 'category_id' => 1, 'perishable' => 1, 'min_stock' => 50 ],
            [ 'name' => 'Chicken cuts (legs, wings, breast)', 'sku' => 'SKU-CHK-CUTS', 'barcode' => '2234567890123', 'category_id' => 1, 'perishable' => 1, 'min_stock' => 40 ],
            [ 'name' => 'Processed chicken (marinated)', 'sku' => 'SKU-CHK-PROC', 'barcode' => '3234567890123', 'category_id' => 1, 'perishable' => 1, 'min_stock' => 30 ],
        ];
        $this->db->table('items')->emptyTable();
        $this->db->table('items')->insertBatch($items);
    }
}


