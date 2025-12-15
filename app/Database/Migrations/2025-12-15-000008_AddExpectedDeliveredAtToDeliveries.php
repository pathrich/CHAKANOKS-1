<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExpectedDeliveredAtToDeliveries extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('deliveries')) {
            return;
        }

        if (! $this->db->fieldExists('expected_delivered_at', 'deliveries')) {
            $this->forge->addColumn('deliveries', [
                'expected_delivered_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'scheduled_at',
                ],
            ]);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('deliveries')) {
            return;
        }

        if ($this->db->fieldExists('expected_delivered_at', 'deliveries')) {
            $this->forge->dropColumn('deliveries', 'expected_delivered_at');
        }
    }
}
