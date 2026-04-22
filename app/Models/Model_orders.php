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
