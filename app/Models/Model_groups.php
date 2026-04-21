<?php

namespace App\Models;

use CodeIgniter\Model;

class Model_groups extends Model
{
    protected $table = 'user_groups';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'group_name',
        'permission',
    ];

    public function getGroupData($id = null)
    {
        if ($id) {
            return $this->find($id);
        }

        return $this->orderBy('id', 'desc')->findAll();
    }

    public function create(array $data)
    {
        return $this->insert($data, true);
    }

    public function edit(array $data, $id): bool
    {
        return $this->update($id, $data);
    }

    public function deleteGroup($id): bool
    {
        if (! $id) {
            return false;
        }

        return (bool) $this->delete($id);
    }

    public function existInUserGroup($groupId): bool
    {
        $row = $this->db->table('user_group')
            ->select('id')
            ->where('group_id', $groupId)
            ->limit(1)
            ->get()
            ->getRowArray();

        return ! empty($row);
    }
}
