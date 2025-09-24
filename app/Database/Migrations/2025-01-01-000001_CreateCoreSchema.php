<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreSchema extends Migration
{
    public function up()
    {
        // branches
        $this->forge->addField([
            'id' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true ],
            'name' => [ 'type' => 'VARCHAR', 'constraint' => 100 ],
            'code' => [ 'type' => 'VARCHAR', 'constraint' => 30, 'unique' => true ],
            'city' => [ 'type' => 'VARCHAR', 'constraint' => 100, 'null' => true ],
            'created_at' => [ 'type' => 'DATETIME', 'null' => true ],
            'updated_at' => [ 'type' => 'DATETIME', 'null' => true ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('branches', true);

        // roles
        $this->forge->addField([
            'id' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true ],
            'name' => [ 'type' => 'VARCHAR', 'constraint' => 50, 'unique' => true ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('roles', true);

        // users
        $this->forge->addField([
            'id' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true ],
            'username' => [ 'type' => 'VARCHAR', 'constraint' => 50, 'unique' => true ],
            'password_hash' => [ 'type' => 'VARCHAR', 'constraint' => 255 ],
            'full_name' => [ 'type' => 'VARCHAR', 'constraint' => 100, 'null' => true ],
            'branch_id' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true ],
            'created_at' => [ 'type' => 'DATETIME', 'null' => true ],
            'updated_at' => [ 'type' => 'DATETIME', 'null' => true ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('users', true);

        // user_roles (many-to-many)
        $this->forge->addField([
            'user_id' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true ],
            'role_id' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true ],
        ]);
        $this->forge->addKey(['user_id','role_id'], true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_roles', true);

        // item_categories
        $this->forge->addField([
            'id' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true ],
            'name' => [ 'type' => 'VARCHAR', 'constraint' => 100, 'unique' => true ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('item_categories', true);

        // items
        $this->forge->addField([
            'id' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true ],
            'name' => [ 'type' => 'VARCHAR', 'constraint' => 150 ],
            'sku' => [ 'type' => 'VARCHAR', 'constraint' => 50, 'unique' => true ],
            'barcode' => [ 'type' => 'VARCHAR', 'constraint' => 64, 'null' => true ],
            'category_id' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true ],
            'perishable' => [ 'type' => 'TINYINT', 'constraint' => 1, 'default' => 0 ],
            'min_stock' => [ 'type' => 'INT', 'constraint' => 11, 'default' => 0 ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('category_id', 'item_categories', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('items', true);

        // branch_stocks (by item, branch, optional expiry lot)
        $this->forge->addField([
            'id' => [ 'type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true ],
            'branch_id' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true ],
            'item_id' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true ],
            'quantity' => [ 'type' => 'INT', 'constraint' => 11, 'default' => 0 ],
            'expiry_date' => [ 'type' => 'DATE', 'null' => true ],
            'created_at' => [ 'type' => 'DATETIME', 'null' => true ],
            'updated_at' => [ 'type' => 'DATETIME', 'null' => true ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['branch_id','item_id']);
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('item_id', 'items', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('branch_stocks', true);

        // activity_logs
        $this->forge->addField([
            'id' => [ 'type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true ],
            'user_id' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true ],
            'action' => [ 'type' => 'VARCHAR', 'constraint' => 100 ],
            'details' => [ 'type' => 'TEXT', 'null' => true ],
            'created_at' => [ 'type' => 'DATETIME', 'null' => true ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('activity_logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('activity_logs', true);
        $this->forge->dropTable('branch_stocks', true);
        $this->forge->dropTable('items', true);
        $this->forge->dropTable('item_categories', true);
        $this->forge->dropTable('user_roles', true);
        $this->forge->dropTable('users', true);
        $this->forge->dropTable('roles', true);
        $this->forge->dropTable('branches', true);
    }
}


