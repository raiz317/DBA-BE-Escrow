<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'product_id',
        'username',
        'rank',
        'price',
        'image',
        'description'
    ];
}