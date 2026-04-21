<?php

namespace App\Models;

class RoleModel extends BaseModel
{
    protected $table      = 'roles';
    protected $primaryKey = 'id';

    protected $allowedFields = ['name', 'description', 'is_active'];

    protected $validationRules = [
        'name' => 'required|max_length[100]|is_unique[roles.name,id,{id}]',
    ];

    /** Get role with its permission list. */
    public function findWithPermissions(int $roleId): ?array
    {
        $role = $this->find($roleId);
        if (! $role) {
            return null;
        }
        $role['permissions'] = $this->db->table('role_permissions rp')
            ->select('p.id, p.name, p.module, p.description')
            ->join('permissions p', 'p.id = rp.permission_id')
            ->where('rp.role_id', $roleId)
            ->get()->getResultArray();
        return $role;
    }

    /** Sync permissions for a role (replaces existing). */
    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $this->db->table('role_permissions')->where('role_id', $roleId)->delete();
        if (! empty($permissionIds)) {
            $rows = array_map(fn($pid) => ['role_id' => $roleId, 'permission_id' => $pid], $permissionIds);
            $this->db->table('role_permissions')->insertBatch($rows);
        }
    }
}
