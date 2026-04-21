<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReturnFlagToOrdersItem extends Migration
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

        if ($table && ! $db->fieldExists('returned', $table)) {
            $this->forge->addColumn($table, [
                'returned' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'after' => 'amount',
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

        if ($table && $db->fieldExists('returned', $table)) {
            $this->forge->dropColumn($table, 'returned');
        }
    }
}
