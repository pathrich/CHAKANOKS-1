<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSupplyRequestSchema extends Migration
{
    public function up()
    {
        // supply_requests table
        $this->forge->addField([
            'id'              => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'branch_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'requested_by'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'status'          => ['type' => 'ENUM', 'constraint' => ['Pending', 'Approved', 'Rejected', 'Fulfilled'], 'default' => 'Pending'],
            'total_items'     => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'notes'           => ['type' => 'TEXT', 'null' => true],
            'approved_by'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'approved_at'     => ['type' => 'DATETIME', 'null' => true],
            'rejected_reason' => ['type' => 'TEXT', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['branch_id', 'status']);
        $this->forge->addKey('requested_by');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('requested_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('approved_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('supply_requests', true);

        // supply_request_items table (line items in each request)
        $this->forge->addField([
            'id'                  => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'supply_request_id'   => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'item_id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'quantity_requested'  => ['type' => 'INT', 'constraint' => 11],
            'quantity_approved'   => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'notes'               => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('supply_request_id');
        $this->forge->addForeignKey('supply_request_id', 'supply_requests', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('item_id', 'items', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('supply_request_items', true);

        // notifications table
        $this->forge->addField([
            'id'              => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'recipient_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'type'            => ['type' => 'VARCHAR', 'constraint' => 50],
            'title'           => ['type' => 'VARCHAR', 'constraint' => 255],
            'message'         => ['type' => 'TEXT'],
            'related_id'      => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'related_type'    => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'is_read'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'read_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['recipient_id', 'is_read']);
        $this->forge->addForeignKey('recipient_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('notifications', true);
    }

    public function down()
    {
        $this->forge->dropTable('notifications', true);
        $this->forge->dropTable('supply_request_items', true);
        $this->forge->dropTable('supply_requests', true);
    }
}
