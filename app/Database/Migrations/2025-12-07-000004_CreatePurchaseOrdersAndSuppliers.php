<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePurchaseOrdersAndSuppliers extends Migration
{
    public function up()
    {
        // suppliers table
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'contact_name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'contact_email' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'contact_phone' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'address' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('suppliers', true);

        // purchase_orders table
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'supply_request_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'supplier_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['PO_CREATED','SUPPLIER_CONFIRMED','SUPPLIER_REQUESTED_CHANGES','SUPPLIER_DECLINED','SHIPPED','DELIVERED','COMPLETED','CANCELLED'], 'default' => 'PO_CREATED'],
            'po_number' => ['type' => 'VARCHAR', 'constraint' => 100, 'unique' => true],
            'total_items' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'total_amount' => ['type' => 'DECIMAL', 'constraint' => [12,2], 'default' => 0],
            'tracking_number' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['supply_request_id','supplier_id']);
        $this->forge->addForeignKey('supply_request_id', 'supply_requests', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('supplier_id', 'suppliers', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('purchase_orders', true);

        // purchase_order_items table
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'purchase_order_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'item_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'quantity' => ['type' => 'INT', 'constraint' => 11],
            'unit_price' => ['type' => 'DECIMAL', 'constraint' => [12,2]],
            'subtotal' => ['type' => 'DECIMAL', 'constraint' => [12,2]],
            'notes' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('purchase_order_id');
        $this->forge->addForeignKey('purchase_order_id', 'purchase_orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('item_id', 'items', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('purchase_order_items', true);

        // audit_logs table (captures user, role, action, details)
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'role' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'action' => ['type' => 'VARCHAR', 'constraint' => 150],
            'details' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('audit_logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('audit_logs', true);
        $this->forge->dropTable('purchase_order_items', true);
        $this->forge->dropTable('purchase_orders', true);
        $this->forge->dropTable('suppliers', true);
    }
}
