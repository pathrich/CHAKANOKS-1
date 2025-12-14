<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run()
    {
        // Ensure categories exist (supports running this seeder standalone)
        $catCount = 0;
        try {
            if ($this->db->tableExists('item_categories')) {
                $catCount = (int) $this->db->table('item_categories')->countAllResults();
            }
        } catch (\Throwable $e) {
            $catCount = 0;
        }

        if ($catCount === 0) {
            $this->db->table('item_categories')->insertBatch([
                [ 'name' => 'Produce' ],
                [ 'name' => 'Meat & Poultry' ],
                [ 'name' => 'Dairy' ],
                [ 'name' => 'Frozen' ],
            ]);
        }

        $categories = $this->db->table('item_categories')->select('id,name')->get()->getResultArray();
        $catMap = [];
        foreach ($categories as $c) {
            $catMap[$c['name']] = (int) $c['id'];
        }

        $items = [
<<<<<<< HEAD
            [ 'name' => 'Whole chicken', 'sku' => 'SKU-CHK-WHOLE', 'barcode' => '1234567890123', 'category_id' => 1, 'perishable' => 1, 'min_stock' => 50 ],
            [ 'name' => 'Chicken cuts (legs, wings, breast)', 'sku' => 'SKU-CHK-CUTS', 'barcode' => '2234567890123', 'category_id' => 1, 'perishable' => 1, 'min_stock' => 40 ],
            [ 'name' => 'Processed chicken (marinated)', 'sku' => 'SKU-CHK-PROC', 'barcode' => '3234567890123', 'category_id' => 1, 'perishable' => 1, 'min_stock' => 30 ],
=======
            [ 'name' => 'Organic Apples', 'sku' => 'SKU-APP-001', 'barcode' => '1234567890123', 'category_id' => $catMap['Produce'] ?? null, 'perishable' => 1, 'min_stock' => 50 ],
            [ 'name' => 'Ground Beef', 'sku' => 'SKU-BEF-001', 'barcode' => '2234567890123', 'category_id' => $catMap['Meat & Poultry'] ?? null, 'perishable' => 1, 'min_stock' => 30 ],
            [ 'name' => 'Whole Milk', 'sku' => 'SKU-MLK-001', 'barcode' => '3234567890123', 'category_id' => $catMap['Dairy'] ?? null, 'perishable' => 1, 'min_stock' => 20 ],
>>>>>>> 7b34fa832e84a49ca2de74d7a657b36ec355deaf
        ];
        $this->db->table('items')->emptyTable();
        $this->db->table('items')->insertBatch($items);
    }
}


