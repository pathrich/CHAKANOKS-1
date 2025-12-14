<?php

namespace App\Controllers;

use App\Models\ItemModel;

class Items extends BaseController
{
    public function index()
    {
        $itemModel = new ItemModel();
        $items = $itemModel->findAllWithCategories();

        $data = [
            'title' => 'Items Management',
            'items' => $items,
        ];

        return view('items/index', $data);
    }

    public function store()
    {
        $itemModel = new ItemModel();

        $data = [
            'name'            => $this->request->getPost('name'),
            'sku'             => $this->request->getPost('sku'),
            'description'     => $this->request->getPost('description'),
            'category_id'     => (int)$this->request->getPost('category_id') ?: null,
            'unit_price'      => (float)$this->request->getPost('unit_price'),
            'stock_quantity'  => 0,
            'min_stock_level' => (int)$this->request->getPost('min_stock_level') ?: 0,
            'max_stock_level' => (int)$this->request->getPost('max_stock_level') ?: 0,
            'is_active'       => 1,
        ];

        if (empty($data['name']) || empty($data['sku'])) {
            return redirect()->back()->with('error', 'Name and SKU are required');
        }

        $itemModel->insert($data);

        return redirect()->back()->with('success', 'Item created successfully');
    }
}
