<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceRow extends Model
{
    protected $fillable = [
        'price_file_id',
        'supplier_sku',
        'manufacturer_sku',
        'manufacturer_name',
        'barcode',
        'name',
        'category',
        'price',
        'rrp',
        'quantity',
        'availability_status',
        'raw',
    ];

    protected $casts = [
        'raw' => 'array',
    ];

    public function priceFile()
    {
        return $this->belongsTo(PriceFile::class);
    }
}
