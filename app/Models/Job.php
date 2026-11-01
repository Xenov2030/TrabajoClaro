<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }

    public function payments()
    {
        return $this->hasMany(PaymentEntry::class);
    }

    public function balance(): int
    {
        return $this->amount_minor - (int) $this->payments()->whereNull('voided_at')->sum('amount_minor');
    }
}
