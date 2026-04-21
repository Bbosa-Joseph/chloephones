<?php

namespace App\Models;

class BrandModel extends BaseModel
{
    protected $table      = 'brands';
    protected $primaryKey = 'id';

    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $useTimestamps  = false;

    protected $allowedFields = ['name', 'description', 'is_active'];
}
