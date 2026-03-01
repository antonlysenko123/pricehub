<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceMatrix extends Model
{
    protected $table = 'price_matrix';

    protected $fillable = [
        'product_id',
        'supplier_id',
        'supplier_sku',
        'manufacturer_sku',
        'manufacturer_name',
        'barcode',
        'last_price_row_id',
        'last_price',
        'last_rrp',
        'last_price_updated_at',
    ];

    protected $casts = [
        'last_price' => 'float',
        'last_rrp' => 'float',
        'last_price_updated_at' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function lastPriceRow()
    {
        return $this->belongsTo(PriceRow::class, 'last_price_row_id');
    }
}
