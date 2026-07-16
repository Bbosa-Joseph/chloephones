<?php

namespace App\Models;

use CodeIgniter\Model;

class Model_orders extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'bill_no',
        'customer_name',
        'customer_phone',
        'customer_address',
        'date_time',
        'gross_amount',
        'service_charge_rate',
        'service_charge',
        'vat_charge_rate',
        'vat_charge',
        'net_amount',
        'discount',
        'paid_status',
        'user_id',
    ];

    public function getItemsTableName(): string
    {
        if ($this->db->tableExists('orders_item')) {
            return 'orders_item';
        }
        if ($this->db->tableExists('order_items')) {
            return 'order_items';
        }
        return 'orders_item';
    }

    public function getOrdersData($id = null)
    {
        if ($id) {
            return $this->find($id);
        }

        return $this->orderBy('id', 'desc')->findAll();
    }

    public function getOrdersItemData($orderId): array
    {
        $itemsTable = $this->getItemsTableName();
        return $this->db->table($itemsTable)
            ->where('order_id', $orderId)
            ->get()
            ->getResultArray();
    }

    public function generateBillNo(): string
    {
        return $this->formatBillNo($this->getStoredOrFallbackNextBillNumber());
    }

    private function getFallbackNextBillNumber(): int
    {
        $nextNumber = $this->getNextOrderSequenceNumber();

        $lastNumericBill = $this->select('bill_no')
            ->where("bill_no REGEXP '^[0-9]+$'", null, false)
            ->orderBy('CAST(bill_no AS UNSIGNED)', 'DESC', false)
            ->first();

        if (! empty($lastNumericBill['bill_no'])) {
            $nextNumber = max($nextNumber, ((int) $lastNumericBill['bill_no']) + 1);
        }

        return $nextNumber;
    }

    private function getNextOrderSequenceNumber(): int
    {
        $tableName = $this->db->prefixTable($this->table);
        $status = $this->db->query('SHOW TABLE STATUS LIKE ?', [$tableName])->getRowArray();

        if (! empty($status['Auto_increment'])) {
            return (int) $status['Auto_increment'];
        }

        return 1;
    }

    private function getStoredOrFallbackNextBillNumber(): int
    {
        $nextNumber = $this->getFallbackNextBillNumber();

        if (! $this->db->tableExists('company_settings') || ! $this->db->fieldExists('next_receipt_number', 'company_settings')) {
            return $nextNumber;
        }

        $settings = $this->db->table('company_settings')
            ->select('next_receipt_number')
            ->where('id', 1)
            ->get()
            ->getRowArray();

        if (! empty($settings['next_receipt_number'])) {
            $nextNumber = max($nextNumber, (int) $settings['next_receipt_number']);
        }

        return $nextNumber;
    }

    private function reserveBillNo(): string
    {
        $nextNumber = $this->getStoredOrFallbackNextBillNumber();

        if ($this->db->tableExists('company_settings') && $this->db->fieldExists('next_receipt_number', 'company_settings')) {
            $settingsTable = $this->db->table('company_settings');
            $existing = $settingsTable->where('id', 1)->get()->getRowArray();
            $payload = ['next_receipt_number' => $nextNumber + 1];

            if ($existing) {
                $settingsTable->where('id', 1)->update($payload);
            } else {
                $settingsTable->insert(['id' => 1] + $payload);
            }
        }

        return $this->formatBillNo($nextNumber);
    }

    private function formatBillNo(int $number): string
    {
        return str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    public function createOrder(array $orderData, array $items): ?int
    {
        if (empty($orderData['bill_no'])) {
            $orderData['bill_no'] = $this->reserveBillNo();
        }

        $this->db->transStart();

        $orderId = $this->insert($orderData, true);
        if ($orderId) {
            $itemsTable = $this->getItemsTableName();
            $hasReturnFlag = $this->db->fieldExists('returned', $itemsTable);
            $hasSnapshot = $this->db->fieldExists('product_name', $itemsTable);

            $productIds = array_values(array_unique(array_filter(array_column($items, 'product_id'))));
            $productMap = [];
            if (! empty($productIds)) {
                $productRows = $this->db->table('products')
                    ->whereIn('id', $productIds)
                    ->get()
                    ->getResultArray();
                foreach ($productRows as $productRow) {
                    $productMap[(int) $productRow['id']] = $productRow;
                }
            }

            $rows = [];
            foreach ($items as $item) {
                $productId = $item['product_id'] ?? null;
                $product = $productId ? ($productMap[(int) $productId] ?? []) : [];
                $row = [
                    'order_id' => $orderId,
                    'product_id' => $productId,
                    'rate' => $item['rate'] ?? 0,
                    'amount' => $item['amount'] ?? 0,
                ];
                if ($hasSnapshot) {
                    $row['product_name'] = $product['name'] ?? null;
                    $row['product_imei'] = $product['imei'] ?? null;
                    $row['product_price'] = $product['price'] ?? null;
                    $row['product_storage'] = $product['storage'] ?? null;
                    $row['product_ram'] = $product['ram'] ?? null;
                    $row['product_warehouse_id'] = $product['warehouse_id'] ?? null;
                }
                if ($hasReturnFlag) {
                    $row['returned'] = 0;
                }
                $rows[] = $row;
            }

            if (! empty($rows)) {
                $this->db->table($itemsTable)->insertBatch($rows);
            }

            if (! empty($productIds)) {
                $this->db->table('products')
                    ->whereIn('id', $productIds)
                    ->delete();
            }
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false || ! $orderId) {
            return null;
        }

        return (int) $orderId;
    }

    public function updateOrder($id, array $orderData, array $items, array $existingItems = []): bool
    {
        $this->db->transStart();

        $this->update($id, $orderData);
        $itemsTable = $this->getItemsTableName();
        $this->db->table($itemsTable)->where('order_id', $id)->delete();

        $hasReturnFlag = $this->db->fieldExists('returned', $itemsTable);
        $hasSnapshot = $this->db->fieldExists('product_name', $itemsTable);

        $productIds = array_values(array_unique(array_filter(array_column($items, 'product_id'))));
        $productMap = [];
        if (! empty($productIds)) {
            $productRows = $this->db->table('products')
                ->whereIn('id', $productIds)
                ->get()
                ->getResultArray();
            foreach ($productRows as $productRow) {
                $productMap[(int) $productRow['id']] = $productRow;
            }
        }

        $rows = [];
        foreach ($items as $item) {
            $productId = $item['product_id'] ?? null;
            $product = $productId ? ($productMap[(int) $productId] ?? []) : [];
            $row = [
                'order_id' => $id,
                'product_id' => $productId,
                'rate' => $item['rate'] ?? 0,
                'amount' => $item['amount'] ?? 0,
            ];
            if ($hasSnapshot) {
                $row['product_name'] = $product['name'] ?? null;
                $row['product_imei'] = $product['imei'] ?? null;
                $row['product_price'] = $product['price'] ?? null;
                $row['product_storage'] = $product['storage'] ?? null;
                $row['product_ram'] = $product['ram'] ?? null;
                $row['product_warehouse_id'] = $product['warehouse_id'] ?? null;
            }
            if ($hasReturnFlag) {
                $row['returned'] = 0;
            }
            $rows[] = $row;
        }

        if (! empty($rows)) {
            $this->db->table($itemsTable)->insertBatch($rows);
        }

        $newProductIds = array_values(array_unique(array_filter(array_column($rows, 'product_id'))));
        if (! empty($newProductIds)) {
            $this->db->table('products')
                ->whereIn('id', $newProductIds)
                ->delete();
        }

        $this->db->transComplete();

        return (bool) $this->db->transStatus();
    }

    public function remove($id): bool
    {
        if (! $id) {
            return false;
        }

        $this->db->transStart();
        $itemsTable = $this->getItemsTableName();
        $this->db->table($itemsTable)->where('order_id', $id)->delete();
        $this->delete($id);
        $this->db->transComplete();

        return (bool) $this->db->transStatus();
    }

    public function countTotalPaidOrders(): int
    {
        return (int) $this->where('paid_status', 1)->countAllResults();
    }

    public function countTotalUnpaidOrders(): int
    {
        return (int) $this->where('paid_status !=', 1)->countAllResults();
    }
}
