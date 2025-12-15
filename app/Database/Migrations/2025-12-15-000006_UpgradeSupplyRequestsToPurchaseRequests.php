<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpgradeSupplyRequestsToPurchaseRequests extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('supply_requests')) {
            return;
        }

        if (! $this->db->fieldExists('preferred_supplier_id', 'supply_requests')) {
            $this->forge->addColumn('supply_requests', [
                'preferred_supplier_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'requested_by',
                ],
            ]);
        }

        if (! $this->db->fieldExists('required_delivery_date', 'supply_requests')) {
            $this->forge->addColumn('supply_requests', [
                'required_delivery_date' => [
                    'type' => 'DATE',
                    'null' => true,
                    'after' => 'preferred_supplier_id',
                ],
            ]);
        }

        if (! $this->db->fieldExists('branch_approved_by', 'supply_requests')) {
            $this->forge->addColumn('supply_requests', [
                'branch_approved_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'required_delivery_date',
                ],
            ]);
        }

        if (! $this->db->fieldExists('branch_approved_at', 'supply_requests')) {
            $this->forge->addColumn('supply_requests', [
                'branch_approved_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'branch_approved_by',
                ],
            ]);
        }

        if (! $this->db->fieldExists('branch_rejected_reason', 'supply_requests')) {
            $this->forge->addColumn('supply_requests', [
                'branch_rejected_reason' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'branch_approved_at',
                ],
            ]);
        }

        if ($this->db->fieldExists('status', 'supply_requests')) {
            $this->forge->modifyColumn('supply_requests', [
                'status' => [
                    'type' => 'ENUM',
                    'constraint' => [
                        'Pending Branch Approval',
                        'Pending Central Approval',
                        'Approved',
                        'Rejected By Branch',
                        'Rejected By Central',
                        'Fulfilled',
                    ],
                    'default' => 'Pending Branch Approval',
                ],
            ]);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('supply_requests')) {
            return;
        }

        if ($this->db->fieldExists('preferred_supplier_id', 'supply_requests')) {
            $this->forge->dropColumn('supply_requests', 'preferred_supplier_id');
        }

        if ($this->db->fieldExists('required_delivery_date', 'supply_requests')) {
            $this->forge->dropColumn('supply_requests', 'required_delivery_date');
        }

        if ($this->db->fieldExists('branch_approved_by', 'supply_requests')) {
            $this->forge->dropColumn('supply_requests', 'branch_approved_by');
        }

        if ($this->db->fieldExists('branch_approved_at', 'supply_requests')) {
            $this->forge->dropColumn('supply_requests', 'branch_approved_at');
        }

        if ($this->db->fieldExists('branch_rejected_reason', 'supply_requests')) {
            $this->forge->dropColumn('supply_requests', 'branch_rejected_reason');
        }

        if ($this->db->fieldExists('status', 'supply_requests')) {
            $this->forge->modifyColumn('supply_requests', [
                'status' => [
                    'type' => 'ENUM',
                    'constraint' => ['Pending', 'Approved', 'Rejected', 'Fulfilled'],
                    'default' => 'Pending',
                ],
            ]);
        }
    }
}
