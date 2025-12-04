<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;

class Items extends Controller
{
    use ResponseTrait;

    /**
     * Get list of all items
     */
    public function list()
    {
        if (!session('user_id')) {
            return $this->failUnauthorized('Not authenticated');
        }

        $db = db_connect();
        $items = $db->table('items')
                    ->select('items.id, items.name, items.sku, items.category_id, item_categories.name as category')
                    ->join('item_categories', 'item_categories.id = items.category_id', 'LEFT')
                    ->get()
                    ->getResultArray();

        return $this->respond($items);
    }
}
