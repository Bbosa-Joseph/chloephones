<?php

namespace App\Models;

class PurchaseItemModel extends BaseModel
{
    protected $table      = 'purchase_items';
    protected $primaryKey = 'id';

    protected $useTimestamps = false;

    protected $allowedFields = [
        'purchase_id', 'product_id', 'variant_id', 'product_item_id',
        'qty', 'unit_cost', 'total_cost',
    ];
}
