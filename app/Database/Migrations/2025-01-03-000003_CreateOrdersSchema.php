<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrdersSchema extends Migration
{
    public function up()
    {
        // orders table
        $this->forge->addField([
            'id'              => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'branch_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_by'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'status'          => ['type' => 'ENUM', 'constraint' => ['Draft', 'Pending', 'Approved', 'Shipped', 'Delivered', 'Cancelled'], 'default' => 'Draft'],
            'order_number'    => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'total_items'     => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'total_amount'    => ['type' => 'DECIMAL', 'constraint' => [10, 2], 'default' => 0],
            'notes'           => ['type' => 'TEXT', 'null' => true],
            'approved_by'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'approved_at'     => ['type' => 'DATETIME', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['branch_id', 'status']);
        $this->forge->addKey('created_by');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('approved_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('orders', true);

        // order_items table (line items in each order)
        $this->forge->addField([
            'id'              => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'order_id'        => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'item_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'quantity'        => ['type' => 'INT', 'constraint' => 11],
            'unit_price'      => ['type' => 'DECIMAL', 'constraint' => [10, 2]],
            'subtotal'        => ['type' => 'DECIMAL', 'constraint' => [10, 2]],
            'notes'           => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('order_id');
        $this->forge->addForeignKey('order_id', 'orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('item_id', 'items', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('order_items', true);
    }

    public function down()
    {
        $this->forge->dropTable('order_items', true);
        $this->forge->dropTable('orders', true);
    }
}
