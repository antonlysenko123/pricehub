<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceFile extends Model
{
    protected $fillable = [
        'supplier_id',
        'filename',
        'original_name',
        'extension',
        'status',
        'rows_count',
        'progress',
        'current_action',
        'error_message',
        'price_date',
    ];

    protected $casts = [
        'price_date' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function rows()
    {
        return $this->hasMany(PriceRow::class);
    }
}
