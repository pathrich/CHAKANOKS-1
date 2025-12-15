<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBranchDetails extends Migration
{
    public function up()
    {
        // Add optional address, contact number, and status columns to branches
        $fields = [
            'address' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'city',
            ],
            'contact_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'address',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'contact_number',
            ],
        ];

        $this->forge->addColumn('branches', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('branches', ['address', 'contact_number', 'status']);
    }
}
