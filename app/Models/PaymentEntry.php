<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentEntry extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['voided_at' => 'datetime'];
    }
}
