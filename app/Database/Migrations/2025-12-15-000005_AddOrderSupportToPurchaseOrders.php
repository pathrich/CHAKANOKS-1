<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOrderSupportToPurchaseOrders extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('purchase_orders')) {
            return;
        }

        $fields = [];

        if (! $this->db->fieldExists('order_id', 'purchase_orders')) {
            $fields['order_id'] = [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
                'after' => 'id',
            ];
        }

        if (! $this->db->fieldExists('branch_id', 'purchase_orders')) {
            $fields['branch_id'] = [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'order_id',
            ];
        }

        if (!empty($fields)) {
            $this->forge->addColumn('purchase_orders', $fields);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('purchase_orders')) {
            return;
        }

        if ($this->db->fieldExists('order_id', 'purchase_orders')) {
            $this->forge->dropColumn('purchase_orders', 'order_id');
        }

        if ($this->db->fieldExists('branch_id', 'purchase_orders')) {
            $this->forge->dropColumn('purchase_orders', 'branch_id');
        }
    }
}
