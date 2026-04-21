<?php

namespace App\Models;

use CodeIgniter\Model;

class Model_users extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'username',
        'password',
        'email',
        'firstname',
        'lastname',
        'phone',
        'gender',
    ];

    public function getUserData($id = null)
    {
        if ($id) {
            return $this->find($id);
        }

        return $this->orderBy('id', 'desc')->findAll();
    }

    public function getUserGroup($userId): ?array
    {
        return $this->db->table('user_group ug')
            ->select('g.*')
            ->join('user_groups g', 'g.id = ug.group_id', 'left')
            ->where('ug.user_id', $userId)
            ->get()
            ->getRowArray();
    }

    public function create(array $data, $groupId)
    {
        $this->db->transStart();

        $userId = $this->insert($data, true);
        if ($userId && $groupId) {
            $this->db->table('user_group')->insert([
                'user_id' => $userId,
                'group_id' => $groupId,
            ]);
        }

        $this->db->transComplete();

        return $this->db->transStatus() ? $userId : false;
    }

    public function edit(array $data, $id, $groupId = null): bool
    {
        $this->db->transStart();

        $this->update($id, $data);
        if ($groupId !== null) {
            $this->db->table('user_group')->where('user_id', $id)->delete();
            $this->db->table('user_group')->insert([
                'user_id' => $id,
                'group_id' => $groupId,
            ]);
        }

        $this->db->transComplete();

        return (bool) $this->db->transStatus();
    }

    public function deleteUser($id): bool
    {
        if (! $id) {
            return false;
        }

        $this->db->transStart();
        $this->db->table('user_group')->where('user_id', $id)->delete();
        $this->delete($id);
        $this->db->transComplete();

        return (bool) $this->db->transStatus();
    }

    public function countTotalUsers(): int
    {
        return (int) $this->countAllResults();
    }
}
