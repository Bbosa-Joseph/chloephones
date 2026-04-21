<?php

namespace App\Models;

class StockLevelModel extends BaseModel
{
    protected $table      = 'stock_levels';
    protected $primaryKey = 'id';

    protected $useTimestamps = false;  // only has updated_at, no created_at

    protected $allowedFields = [
        'product_id', 'variant_id',
        'warehouse_id', 'store_id',
        'qty_on_hand', 'reorder_level',
    ];

    /** Upsert: add qty to existing row or create it. */
    public function addQty(
        int     $productId,
        int     $qty,
        string  $locationType,
        int     $locationId,
        ?int    $variantId = null
    ): void {
        $col      = $locationType === 'warehouse' ? 'warehouse_id' : 'store_id';
        $otherCol = $locationType === 'warehouse' ? 'store_id' : 'warehouse_id';

        $existing = $this->where('product_id', $productId)
                         ->where($col, $locationId)
                         ->where($otherCol . ' IS NULL')
                         ->where('variant_id', $variantId)
                         ->first();

        if ($existing) {
            $this->update($existing['id'], ['qty_on_hand' => $existing['qty_on_hand'] + $qty]);
        } else {
            $this->insert([
                'product_id'    => $productId,
                'variant_id'    => $variantId,
                $col            => $locationId,
                $otherCol       => null,
                'qty_on_hand'   => $qty,
                'reorder_level' => 0,
            ]);
        }
    }

    /** Return all below-reorder items (for low-stock alerts). */
    public function getBelowReorder(): array
    {
        return $this->db->table('stock_levels sl')
            ->select('sl.*, p.name as product_name, p.sku,
                      pv.variant_sku,
                      w.name as warehouse_name, s.name as store_name')
            ->join('products p',          'p.id = sl.product_id',  'left')
            ->join('product_variants pv', 'pv.id = sl.variant_id', 'left')
            ->join('warehouses w',        'w.id = sl.warehouse_id','left')
            ->join('stores s',            's.id = sl.store_id',    'left')
            ->where('sl.qty_on_hand <= sl.reorder_level')
            ->where('sl.qty_on_hand >', 0) // already zero is a different alert
            ->get()->getResultArray();
    }
}
