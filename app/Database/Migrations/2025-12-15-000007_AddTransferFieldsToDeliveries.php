<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTransferFieldsToDeliveries extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('deliveries')) {
            return;
        }

        if (! $this->db->fieldExists('transfer_id', 'deliveries')) {
            $this->forge->addColumn('deliveries', [
                'transfer_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'order_id',
                ],
            ]);
        }

        if (! $this->db->fieldExists('type', 'deliveries')) {
            $this->forge->addColumn('deliveries', [
                'type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'after' => 'transfer_id',
                ],
            ]);
        }

        // Add FK to stock_transfers if table exists
        if ($this->db->tableExists('stock_transfers')) {
            try {
                $this->forge->addForeignKey('transfer_id', 'stock_transfers', 'id', 'SET NULL', 'CASCADE');
            } catch (\Throwable $e) {
                // ignore if FK already exists or cannot be created
            }
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('deliveries')) {
            return;
        }

        if ($this->db->fieldExists('type', 'deliveries')) {
            $this->forge->dropColumn('deliveries', 'type');
        }

        if ($this->db->fieldExists('transfer_id', 'deliveries')) {
            $this->forge->dropColumn('deliveries', 'transfer_id');
        }
    }
}
