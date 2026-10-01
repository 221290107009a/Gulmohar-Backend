<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;

class ProductSizeInventory extends Model
{
    protected $table = 'product_size_inventory';

    protected $fillable = [
        'product_id',
        'size',
        'qty',
        'in_stock',
        'position',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'in_stock' => 'boolean',
        'qty' => 'integer',
        'position' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
