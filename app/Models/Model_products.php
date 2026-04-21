<?php

namespace App\Models;

use CodeIgniter\Model;

class Model_products extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'name',
        'imei',
        'price',
        'description',
        'storage',
        'ram',
        'warehouse_id',
        'availability',
        'date_added',
    ];

    public function getProductData($id = null)
    {
        if ($id) {
            return $this->find($id);
        }

        return $this->orderBy('id', 'desc')->findAll();
    }

    public function getActiveProductData(): array
    {
        return $this->where('availability', 1)
            ->orderBy('id', 'desc')
            ->findAll();
    }

    public function getProductByIMEI(string $imei): ?array
    {
        return $this->where('imei', $imei)->first();
    }

    public function getProductsByWarehouseIds(array $warehouseIds): array
    {
        if (empty($warehouseIds)) {
            return [];
        }

        return $this->whereIn('warehouse_id', $warehouseIds)
            ->orderBy('id', 'desc')
            ->findAll();
    }

    public function getProductByIdAndWarehouseIds($productId, array $warehouseIds): ?array
    {
        if (empty($warehouseIds)) {
            return null;
        }

        return $this->where('id', $productId)
            ->whereIn('warehouse_id', $warehouseIds)
            ->first();
    }

    public function create(array $data)
    {
        return $this->insert($data, true);
    }

    public function update($data = null, $id = null): bool
    {
        if ($id === null) {
            return false;
        }

        return parent::update($id, $data);
    }

    public function remove($id): bool
    {
        if (! $id) {
            return false;
        }

        return (bool) $this->delete($id);
    }

    public function countTotalProducts(): int
    {
        return (int) $this->countAllResults();
    }

    public function countOutOfStock(): int
    {
        return (int) $this->where('availability !=', 1)->countAllResults();
    }

    public function countAgedProducts(): int
    {
        $cutoff = date('Y-m-d', strtotime('-15 days'));
        return (int) $this->where('date_added <', $cutoff)->countAllResults();
    }

    public function getTotalStockValue(): float
    {
        $row = $this->selectSum('price')
            ->where('availability', 1)
            ->get()
            ->getRowArray();

        return (float) ($row['price'] ?? 0);
    }
}
