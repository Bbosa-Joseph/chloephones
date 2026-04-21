<?php

namespace App\Models;

class OrderModel extends BaseModel
{
    protected $table      = 'orders';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'bill_no', 'customer_id', 'customer_name', 'customer_phone',
        'store_id', 'served_by',
        'gross_amount', 'discount_amount',
        'service_charge_rate', 'service_charge',
        'tax_rate', 'tax_amount',
        'net_amount', 'paid_amount',
        'payment_method', 'payment_status',
        'note',
    ];

    protected $validationRules = [
        'bill_no'   => 'required|max_length[60]|is_unique[orders.bill_no,id,{id}]',
        'store_id'  => 'required|is_natural_no_zero',
        'served_by' => 'required|is_natural_no_zero',
    ];

    public function findWithItems(int $id): ?array
    {
        $order = $this->find($id);
        if (! $order) {
            return null;
        }
        $order['items'] = (new OrderItemModel())
            ->select('oi.*, p.name as product_name, pv.variant_sku')
            ->join('products p',         'p.id = oi.product_id', 'left')
            ->join('product_variants pv','pv.id = oi.variant_id','left')
            ->where('oi.order_id', $id)
            ->findAll();
        return $order;
    }

    /** Daily sales summary for a store. */
    public function getDailySummary(int $storeId, string $date): array
    {
        return $this->db->table('orders')
            ->selectSum('net_amount',      'total_sales')
            ->selectCount('id',            'order_count')
            ->selectSum('discount_amount', 'total_discounts')
            ->where('store_id', $storeId)
            ->where('DATE(ordered_at)', $date)
            ->where('payment_status', 'paid')
            ->get()->getRowArray();
    }

    /** Generate a unique bill number. */
    public static function generateBillNo(string $prefix = 'INV'): string
    {
        return $prefix . '-' . strtoupper(uniqid());
    }
}
