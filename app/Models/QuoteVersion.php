<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteVersion extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['snapshot' => 'array', 'revoked_at' => 'datetime'];
    }
}
