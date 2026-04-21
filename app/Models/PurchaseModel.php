<?php

namespace App\Models;

class PurchaseModel extends BaseModel
{
    protected $table      = 'purchases';
    protected $primaryKey = 'id';

    protected $useTimestamps = false;

    protected $allowedFields = [
        'reference_no', 'supplier_id', 'warehouse_id',
        'total_amount', 'paid_amount', 'payment_status',
        'note', 'purchased_by',
    ];

    protected $validationRules = [
        'reference_no' => 'required|max_length[60]|is_unique[purchases.reference_no,id,{id}]',
        'warehouse_id' => 'required|is_natural_no_zero',
    ];

    public function findWithItems(int $id): ?array
    {
        $purchase = $this->find($id);
        if (! $purchase) {
            return null;
        }
        $purchase['items'] = (new PurchaseItemModel())
            ->select('pi.*, p.name as product_name, pv.variant_sku')
            ->join('products p',         'p.id = pi.product_id',  'left')
            ->join('product_variants pv','pv.id = pi.variant_id', 'left')
            ->where('pi.purchase_id', $id)
            ->findAll();
        return $purchase;
    }
}
