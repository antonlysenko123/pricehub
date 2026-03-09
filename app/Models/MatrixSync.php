<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatrixSync extends Model
{
    protected $fillable = [
        'status',
        'progress',
        'total',
        'processed',
        'current_action',
    ];
}