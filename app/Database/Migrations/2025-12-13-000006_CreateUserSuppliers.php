<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserSuppliers extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'supplier_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey(['user_id', 'supplier_id'], true);
        $this->forge->addKey('supplier_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('supplier_id', 'suppliers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_suppliers', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_suppliers', true);
    }
}
