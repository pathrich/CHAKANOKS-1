<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemModel extends Model
{
    protected $table            = 'items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'sku',
        'description',
        'category_id',
        'unit_price',
        'stock_quantity',
        'min_stock_level',
        'max_stock_level',
        'is_active',
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get all items with category information
     */
    public function findAllWithCategories()
    {
        return $this->select('items.*, item_categories.name as category_name')
                    ->join('item_categories', 'item_categories.id = items.category_id', 'LEFT')
                    ->findAll();
    }

    /**
     * Get active items only
     */
    public function getActiveItems()
    {
        return $this->where('is_active', 1)->findAll();
    }

    /**
     * Get items by category
     */
    public function getByCategory($categoryId)
    {
        return $this->where('category_id', $categoryId)
                    ->where('is_active', 1)
                    ->findAll();
    }

    /**
     * Get low stock items
     */
    public function getLowStockItems()
    {
        return $this->where('stock_quantity <= min_stock_level')
                    ->where('is_active', 1)
                    ->findAll();
    }
}
