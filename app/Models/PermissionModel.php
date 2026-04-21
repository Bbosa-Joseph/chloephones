<?php

namespace App\Models;

class PermissionModel extends BaseModel
{
    protected $table      = 'permissions';
    protected $primaryKey = 'id';

    // permissions don't use updated_at
    protected $useTimestamps = false;

    protected $allowedFields = ['name', 'module', 'description'];

    protected $validationRules = [
        'name'   => 'required|max_length[100]|is_unique[permissions.name,id,{id}]',
        'module' => 'required|max_length[60]',
    ];

    /** Return permissions grouped by module. */
    public function groupedByModule(): array
    {
        $rows = $this->orderBy('module')->orderBy('name')->findAll();
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['module']][] = $row;
        }
        return $grouped;
    }
}
