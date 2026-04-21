<?php

namespace App\Models;

use CodeIgniter\Model;

class Model_notifications extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'user_id',
        'message',
        'type',
        'link',
        'is_read',
        'created_at',
    ];

    public function getUnread($userId): array
    {
        return $this->where('user_id', $userId)
            ->where('is_read', 0)
            ->orderBy('id', 'desc')
            ->findAll(20);
    }

    public function checkNew($userId, $since): array
    {
        return $this->where('user_id', $userId)
            ->where('created_at >', $since)
            ->orderBy('id', 'desc')
            ->findAll();
    }

    public function countUnread($userId): int
    {
        return (int) $this->where('user_id', $userId)
            ->where('is_read', 0)
            ->countAllResults();
    }

    public function markAsRead($userId): bool
    {
        return (bool) $this->where('user_id', $userId)
            ->set('is_read', 1)
            ->update();
    }

    public function notifyWarehouseMembers($warehouseId, string $message, string $type = 'info'): bool
    {
        $store = $this->db->table('stores')
            ->select('assigned_user_id')
            ->where('id', $warehouseId)
            ->get()
            ->getRowArray();

        $userId = $store['assigned_user_id'] ?? null;
        if (! $userId) {
            return false;
        }

        $data = [
            'user_id' => $userId,
            'message' => $message,
            'type' => $type,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        return (bool) $this->insert($data, true);
    }
}
