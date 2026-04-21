<?php

namespace App\Models;

class ReturnModel extends BaseModel
{
    protected $table      = 'returns';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'return_no', 'order_id', 'store_id', 'processed_by',
        'total_amount', 'reason', 'refund_method', 'status', 'note',
    ];

    protected $validationRules = [
        'return_no'    => 'required|max_length[60]|is_unique[returns.return_no,id,{id}]',
        'order_id'     => 'required|is_natural_no_zero',
        'store_id'     => 'required|is_natural_no_zero',
        'processed_by' => 'required|is_natural_no_zero',
        'status'       => 'required|in_list[pending,approved,rejected]',
    ];

    public function findWithItems(int $id): ?array
    {
        $return = $this->find($id);
        if (! $return) {
            return null;
        }
        $return['items'] = (new ReturnItemModel())
            ->select('ri.*, p.name as product_name, pv.variant_sku')
            ->join('products p',         'p.id = ri.product_id', 'left')
            ->join('product_variants pv','pv.id = ri.variant_id','left')
            ->where('ri.return_id', $id)
            ->findAll();
        return $return;
    }

    public static function generateReturnNo(string $prefix = 'RET'): string
    {
        return $prefix . '-' . strtoupper(uniqid());
    }
}
