<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $data = [
            [ 'name' => 'Produce' ],
            [ 'name' => 'Meat & Poultry' ],
            [ 'name' => 'Dairy' ],
            [ 'name' => 'Frozen' ],
        ];
        $this->db->table('item_categories')->emptyTable();
        $this->db->table('item_categories')->insertBatch($data);
    }
}


