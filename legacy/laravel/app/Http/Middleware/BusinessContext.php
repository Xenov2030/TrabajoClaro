<?php

namespace App\Http\Middleware;

use App\Models\Business;
use App\Models\BusinessMembership;
use Closure;
use Illuminate\Http\Request;

class BusinessContext
{
    public function handle(Request $request, Closure $next)
    {
        $membership = BusinessMembership::where('user_id', $request->user()->id)->where('role', 'owner')->firstOrFail();
        $business = Business::findOrFail($membership->business_id);
        abort_if($business->is_demo && $business->expires_at?->isPast(), 403, 'La demo venció. Cerrá sesión e iniciá otra.');
        $request->attributes->set('business', $business);
        view()->share('business', $business);

        return $next($request);
    }
}
