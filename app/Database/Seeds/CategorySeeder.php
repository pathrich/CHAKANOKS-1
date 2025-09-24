<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        db_connect()->table('item_categories')->ignore(true)->insertBatch([
            [ 'name' => 'Produce' ],
            [ 'name' => 'Meat & Poultry' ],
            [ 'name' => 'Dairy' ],
            [ 'name' => 'Frozen' ],
        ]);
    }
}


