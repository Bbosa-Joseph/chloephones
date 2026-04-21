<?php

namespace App\Models;

class ProductModel extends BaseModel
{
    protected $table      = 'products';
    protected $primaryKey = 'id';

    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'name', 'sku', 'category_id', 'brand_id',
        'description', 'cost_price', 'sell_price',
        'inventory_method', 'is_active', 'created_by',
    ];

    protected $validationRules = [
        'name'             => 'required|max_length[255]',
        'category_id'      => 'required|is_natural_no_zero',
        'cost_price'       => 'required|decimal',
        'sell_price'       => 'required|decimal',
        'inventory_method' => 'required|in_list[bulk,serialized]',
    ];

    /** Return a product with category, brand, variants and attribute values. */
    public function findFull(int $id): ?array
    {
        $product = $this->find($id);
        if (! $product) {
            return null;
        }

        // Category & brand are joined for convenience
        $product = $this->db->table('products p')
            ->select('p.*, c.name as category_name, b.name as brand_name')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->join('brands b',     'b.id = p.brand_id',     'left')
            ->where('p.id', $id)
            ->get()->getRowArray();

        $product['variants']   = (new ProductVariantModel())->getByProduct($id);
        $product['attributes'] = (new AttributeValueModel())
            ->select('av.id, av.value, a.name as attribute_name')
            ->join('attributes a', 'a.id = av.attribute_id')
            ->join('product_attributes pa', 'pa.attribute_value_id = av.id')
            ->where('pa.product_id', $id)
            ->findAll();

        return $product;
    }

    /** List products with basic joins, respecting soft delete. */
    public function listWithJoins(array $filters = []): array
    {
        $builder = $this->db->table('products p')
            ->select('p.id, p.name, p.sku, p.cost_price, p.sell_price,
                      p.inventory_method, p.is_active, p.created_at,
                      c.name as category_name, b.name as brand_name')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->join('brands b',     'b.id = p.brand_id',     'left')
            ->where('p.deleted_at IS NULL');

        if (! empty($filters['category_id'])) {
            $builder->where('p.category_id', $filters['category_id']);
        }
        if (! empty($filters['is_active'])) {
            $builder->where('p.is_active', $filters['is_active']);
        }
        if (! empty($filters['search'])) {
            $builder->groupStart()
                    ->like('p.name', $filters['search'])
                    ->orLike('p.sku', $filters['search'])
                    ->groupEnd();
        }

        return $builder->orderBy('p.name')->get()->getResultArray();
    }
}
