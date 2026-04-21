<?php

namespace App\Models;

class AttributeValueModel extends BaseModel
{
    protected $table      = 'attribute_values';
    protected $primaryKey = 'id';

    protected $useTimestamps = false;
    protected $allowedFields = ['attribute_id', 'value'];

    protected $validationRules = [
        'attribute_id' => 'required|is_natural_no_zero',
        'value'        => 'required|max_length[100]',
    ];
}
