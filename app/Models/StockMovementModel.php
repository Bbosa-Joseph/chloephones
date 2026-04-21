<?php

namespace App\Models;

class StockMovementModel extends BaseModel
{
    protected $table      = 'stock_movements';
    protected $primaryKey = 'id';

    protected $useTimestamps = false;  // uses moved_at only

    protected $allowedFields = [
        'product_id', 'variant_id', 'product_item_id',
        'type', 'qty',
        'from_type', 'from_id',
        'to_type',   'to_id',
        'reference_type', 'reference_id',
        'note', 'moved_by',
    ];

    protected $validationRules = [
        'product_id' => 'required|is_natural_no_zero',
        'type'       => 'required|in_list[purchase,sale,transfer_in,transfer_out,adjustment,return,damaged]',
        'qty'        => 'required|integer',
    ];

    /** Record a movement and automatically update stock_levels for bulk products. */
    public function record(array $data, string $inventoryMethod = 'bulk'): int
    {
        $this->db->transStart();

        $this->insert($data);
        $movementId = $this->db->insertID();

        if ($inventoryMethod === 'bulk') {
            $slModel = new StockLevelModel();
            // Deduct from source
            if (! empty($data['from_type']) && ! empty($data['from_id'])) {
                $slModel->addQty(
                    $data['product_id'],
                    -abs($data['qty']),
                    $data['from_type'],
                    $data['from_id'],
                    $data['variant_id'] ?? null
                );
            }
            // Add to destination
            if (! empty($data['to_type']) && ! empty($data['to_id'])) {
                $slModel->addQty(
                    $data['product_id'],
                    abs($data['qty']),
                    $data['to_type'],
                    $data['to_id'],
                    $data['variant_id'] ?? null
                );
            }
        }

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new \RuntimeException('Stock movement transaction failed.');
        }

        return $movementId;
    }

    /** History for a single product (optionally filtered by variant). */
    public function getHistory(int $productId, ?int $variantId = null, int $limit = 50): array
    {
        $q = $this->where('product_id', $productId);
        if ($variantId !== null) {
            $q->where('variant_id', $variantId);
        }
        return $q->orderBy('moved_at', 'DESC')->limit($limit)->findAll();
    }
}
