<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\QuoteVersion;
use App\Services\Workflow;
use Illuminate\Http\Request;

class PublicQuoteController extends Controller
{
    private function version(string $token): QuoteVersion
    {
        abort_unless(preg_match('/^[a-f0-9]{64}$/D', $token), 404);
        $v = QuoteVersion::where('token_hash', hash('sha256', $token))->whereNull('revoked_at')->firstOrFail();
        $b = Business::findOrFail($v->business_id);
        abort_if($b->is_demo && $b->expires_at?->isPast(), 404);

        return $v;
    }

    public function show(string $token)
    {
        $v = $this->version($token);

        return view('quotes.document', ['snapshot' => $v->snapshot, 'version' => $v, 'public' => true]);
    }

    public function decide(Request $r, Workflow $flow, string $token)
    {
        $v = $this->version($token);
        $d = $r->validate(['name' => 'required|string|max:120', 'decision' => 'required|in:aceptado,rechazado,modificacion', 'confirmed' => 'required|accepted', 'message' => 'nullable|string|max:2000']);
        $flow->decide($v, $d);

        return redirect('/p/'.$token)->with('notice', 'Registramos tu decisión sobre esta versión.');
    }
}
