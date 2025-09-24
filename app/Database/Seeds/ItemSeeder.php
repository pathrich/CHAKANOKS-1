<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run()
    {
        db_connect()->table('items')->ignore(true)->insertBatch([
            [ 'name' => 'Organic Apples', 'sku' => 'SKU-APP-001', 'barcode' => '1234567890123', 'category_id' => 1, 'perishable' => 1, 'min_stock' => 50 ],
            [ 'name' => 'Ground Beef', 'sku' => 'SKU-BEF-001', 'barcode' => '2234567890123', 'category_id' => 2, 'perishable' => 1, 'min_stock' => 30 ],
            [ 'name' => 'Whole Milk', 'sku' => 'SKU-MLK-001', 'barcode' => '3234567890123', 'category_id' => 3, 'perishable' => 1, 'min_stock' => 20 ],
        ]);
    }
}


