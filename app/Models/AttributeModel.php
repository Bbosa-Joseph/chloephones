<?php

namespace App\Models;

class AttributeModel extends BaseModel
{
    protected $table      = 'attributes';
    protected $primaryKey = 'id';

    protected $useTimestamps = false;
    protected $allowedFields = ['name', 'is_active'];

    /** Return attribute with its values. */
    public function findWithValues(int $id): ?array
    {
        $attr = $this->find($id);
        if (! $attr) {
            return null;
        }
        $attr['values'] = (new AttributeValueModel())->where('attribute_id', $id)->findAll();
        return $attr;
    }
}
