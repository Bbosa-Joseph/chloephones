<?php

namespace App\Models;

class OrderItemModel extends BaseModel
{
    protected $table      = 'order_items';
    protected $primaryKey = 'id';

    protected $useTimestamps = false;

    protected $allowedFields = [
        'order_id', 'product_id', 'variant_id', 'product_item_id',
        'qty', 'unit_price', 'discount', 'total',
    ];
}
