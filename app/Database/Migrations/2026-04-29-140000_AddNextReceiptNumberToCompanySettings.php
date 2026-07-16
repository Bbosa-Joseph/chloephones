<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNextReceiptNumberToCompanySettings extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (! $db->tableExists('company_settings')) {
            return;
        }

        if (! $db->fieldExists('next_receipt_number', 'company_settings')) {
            $this->forge->addColumn('company_settings', [
                'next_receipt_number' => [
                    'type' => 'INT',
                    'unsigned' => true,
                    'null' => false,
                    'default' => 1,
                    'after' => 'service_charge_rate',
                ],
            ]);
        }

        $nextReceiptNumber = $this->getSeedReceiptNumber($db);
        $settingsTable = $db->table('company_settings');
        $existing = $settingsTable->where('id', 1)->get()->getRowArray();

        if ($existing) {
            $currentValue = isset($existing['next_receipt_number']) ? (int) $existing['next_receipt_number'] : 0;
            $settingsTable->where('id', 1)->update([
                'next_receipt_number' => max($currentValue, $nextReceiptNumber),
            ]);
            return;
        }

        $settingsTable->insert([
            'id' => 1,
            'next_receipt_number' => $nextReceiptNumber,
        ]);
    }

    public function down()
    {
        $db = \Config\Database::connect();

        if ($db->tableExists('company_settings') && $db->fieldExists('next_receipt_number', 'company_settings')) {
            $this->forge->dropColumn('company_settings', 'next_receipt_number');
        }
    }

    private function getSeedReceiptNumber($db): int
    {
        $nextReceiptNumber = 1;

        if ($db->tableExists('orders')) {
            $lastNumericBill = $db->table('orders')
                ->select('bill_no')
                ->where("bill_no REGEXP '^[0-9]+$'", null, false)
                ->orderBy('CAST(bill_no AS UNSIGNED)', 'DESC', false)
                ->get(1)
                ->getRowArray();

            if (! empty($lastNumericBill['bill_no'])) {
                $nextReceiptNumber = max($nextReceiptNumber, ((int) $lastNumericBill['bill_no']) + 1);
            }

            $tableStatus = $db->query('SHOW TABLE STATUS LIKE ?', [$db->prefixTable('orders')])->getRowArray();
            if (! empty($tableStatus['Auto_increment'])) {
                $nextReceiptNumber = max($nextReceiptNumber, (int) $tableStatus['Auto_increment']);
            }
        }

        return $nextReceiptNumber;
    }
}