<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type',
        'source_url',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function priceFiles()
    {
        return $this->hasMany(PriceFile::class);
    }

    public function latestPriceFile()
    {
        return $this->hasOne(PriceFile::class)
            ->latestOfMany();
    }
}
