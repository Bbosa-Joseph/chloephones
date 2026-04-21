<?php

namespace App\Models;

use CodeIgniter\Model;

class Model_stores extends Model
{
    protected $table = 'stores';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'name',
        'active',
        'assigned_user_id',
        'total_stock',
        'total_value',
    ];

    public function getStoresData($id = null)
    {
        if ($id) {
            return $this->find($id);
        }

        return $this->orderBy('id', 'desc')->findAll();
    }

    public function getActiveStore(): array
    {
        return $this->where('active', 1)->orderBy('id', 'desc')->findAll();
    }

    public function getAssignedStores($userId, bool $activeOnly = false): array
    {
        $builder = $this->where('assigned_user_id', $userId);
        if ($activeOnly) {
            $builder->where('active', 1);
        }

        return $builder->orderBy('id', 'desc')->findAll();
    }

    public function getAssignedStoreIds($userId): array
    {
        $rows = $this->select('id')->where('assigned_user_id', $userId)->findAll();
        return array_map(static fn($row) => (int) $row['id'], $rows);
    }

    public function isUserAssignedToStore(int $storeId, int $userId): bool
    {
        return (bool) $this->where('id', $storeId)
            ->where('assigned_user_id', $userId)
            ->first();
    }

    public function getStoreByUserId($userId, $excludeId = null): ?array
    {
        $builder = $this->where('assigned_user_id', $userId);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->first();
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

    public function countTotalStores(): int
    {
        return (int) $this->countAllResults();
    }
}
