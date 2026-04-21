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

    public function createOrder(array $orderData, array $items): ?int
    {
        $this->db->transStart();

        $orderId = $this->insert($orderData, true);
        if ($orderId) {
            $itemsTable = $this->getItemsTableName();
            $hasReturnFlag = $this->db->fieldExists('returned', $itemsTable);
            $rows = [];
            foreach ($items as $item) {
                $row = [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'] ?? null,
                    'rate' => $item['rate'] ?? 0,
                    'amount' => $item['amount'] ?? 0,
                ];
                if ($hasReturnFlag) {
                    $row['returned'] = 0;
                }
                $rows[] = $row;
            }

            if (! empty($rows)) {
                $this->db->table($itemsTable)->insertBatch($rows);
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
        $rows = [];
        foreach ($items as $item) {
            $row = [
                'order_id' => $id,
                'product_id' => $item['product_id'] ?? null,
                'rate' => $item['rate'] ?? 0,
                'amount' => $item['amount'] ?? 0,
            ];
            if ($hasReturnFlag) {
                $row['returned'] = 0;
            }
            $rows[] = $row;
        }

        if (! empty($rows)) {
            $this->db->table($itemsTable)->insertBatch($rows);
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
