<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDeliveries extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true ],
            'order_id' => [ 'type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true ],
            'driver_name' => [ 'type' => 'VARCHAR', 'constraint' => 100, 'null' => true ],
            'vehicle' => [ 'type' => 'VARCHAR', 'constraint' => 100, 'null' => true ],
            'route' => [ 'type' => 'TEXT', 'null' => true ],
            'status' => [ 'type' => 'VARCHAR', 'constraint' => 50, 'default' => 'scheduled' ],
            'scheduled_at' => [ 'type' => 'DATETIME', 'null' => true ],
            'current_location' => [ 'type' => 'VARCHAR', 'constraint' => 255, 'null' => true ],
            'created_by' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true ],
            'created_at' => [ 'type' => 'DATETIME', 'null' => true ],
            'updated_at' => [ 'type' => 'DATETIME', 'null' => true ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('order_id', 'orders', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('deliveries', true);
    }

    public function down()
    {
        $this->forge->dropTable('deliveries', true);
    }
}
