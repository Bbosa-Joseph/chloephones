<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOrderItemSnapshots extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        $table = null;
        if ($db->tableExists('orders_item')) {
            $table = 'orders_item';
        } elseif ($db->tableExists('order_items')) {
            $table = 'order_items';
        }

        if (! $table) {
            return;
        }

        if (! $db->fieldExists('product_name', $table)) {
            $this->forge->addColumn($table, [
                'product_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 190,
                    'null' => true,
                    'after' => 'product_id',
                ],
            ]);
        }
        if (! $db->fieldExists('product_imei', $table)) {
            $this->forge->addColumn($table, [
                'product_imei' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                    'null' => true,
                    'after' => 'product_name',
                ],
            ]);
        }
        if (! $db->fieldExists('product_price', $table)) {
            $this->forge->addColumn($table, [
                'product_price' => [
                    'type' => 'DECIMAL',
                    'constraint' => '12,2',
                    'null' => true,
                    'after' => 'product_imei',
                ],
            ]);
        }
        if (! $db->fieldExists('product_storage', $table)) {
            $this->forge->addColumn($table, [
                'product_storage' => [
                    'type' => 'VARCHAR',
                    'constraint' => 60,
                    'null' => true,
                    'after' => 'product_price',
                ],
            ]);
        }
        if (! $db->fieldExists('product_ram', $table)) {
            $this->forge->addColumn($table, [
                'product_ram' => [
                    'type' => 'VARCHAR',
                    'constraint' => 60,
                    'null' => true,
                    'after' => 'product_storage',
                ],
            ]);
        }
        if (! $db->fieldExists('product_warehouse_id', $table)) {
            $this->forge->addColumn($table, [
                'product_warehouse_id' => [
                    'type' => 'INT',
                    'null' => true,
                    'after' => 'product_ram',
                ],
            ]);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        $table = null;
        if ($db->tableExists('orders_item')) {
            $table = 'orders_item';
        } elseif ($db->tableExists('order_items')) {
            $table = 'order_items';
        }

        if (! $table) {
            return;
        }

        foreach (['product_warehouse_id', 'product_ram', 'product_storage', 'product_price', 'product_imei', 'product_name'] as $col) {
            if ($db->fieldExists($col, $table)) {
                $this->forge->dropColumn($table, $col);
            }
        }
    }
}
