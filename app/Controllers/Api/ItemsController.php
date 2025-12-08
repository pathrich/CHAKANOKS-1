<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ItemModel;

class ItemsController extends BaseController
{
    protected $itemModel;

    public function __construct()
    {
        $this->itemModel = new ItemModel();
    }

    /**
     * Get all items for order creation
     */
    public function index()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX request required']);
        }

        try {
            $items = $this->itemModel->findAll();

            // Format for frontend
            $formattedItems = array_map(function($item) {
                return [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'sku' => $item['sku'],
                    'category' => $item['category_name'] ?? 'Uncategorized'
                ];
            }, $items);

            return $this->response->setJSON($formattedItems);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get items: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to get items']);
        }
    }
}
