<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductEvent extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['details' => 'array'];
    }
}
