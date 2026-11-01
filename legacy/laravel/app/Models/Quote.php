<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['draft' => 'array'];
    }

    public function state(): string
    {
        if ($this->status !== 'pendiente') {
            return $this->status;
        }
        $v = QuoteVersion::where('business_id', $this->business_id)->where('quote_id', $this->id)->whereNull('revoked_at')->latest()->first();
        if ($v && $v->expires_on < now($v->snapshot['business']['timezone'])->toDateString()) {
            return 'vencido';
        }

        return $this->status;
    }
}
