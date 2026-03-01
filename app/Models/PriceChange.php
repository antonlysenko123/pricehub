<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceChange extends Model
{
    protected $fillable = [
        'price_matrix_id',
        'old_price',
        'new_price',
        'changed_at',
    ];

    protected $casts = [
        'old_price' => 'float',
        'new_price' => 'float',
        'changed_at' => 'datetime',
    ];

    public function matrix()
    {
        return $this->belongsTo(PriceMatrix::class, 'price_matrix_id');
    }
}
