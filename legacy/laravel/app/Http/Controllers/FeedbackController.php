<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\FeedbackEntry;
use App\Services\Screen;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeedbackController extends Controller
{
    public function show(Request $request)
    {
        $screen = (string) $request->query('screen', 'other');
        if (! array_key_exists($screen, Screen::LABELS)) {
            $screen = 'other';
        }

        return view('feedback', compact('screen'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['screen' => ['required', Rule::in(array_keys(Screen::LABELS))], 'description' => 'required|string|min:10|max:3000', 'contact' => 'nullable|string|max:190', 'website' => 'nullable|max:0']);
        $businessId = $request->user() ? BusinessMembership::where('user_id', $request->user()->id)->value('business_id') : null;
        FeedbackEntry::create(['screen' => $data['screen'], 'description' => $data['description'], 'contact' => $data['contact'] ?? null, 'release' => config('beta.version'), 'business_id' => $businessId, 'user_id' => $request->user()?->id, 'is_demo' => $businessId ? (bool) Business::findOrFail($businessId)->is_demo : false]);

        return redirect('/feedback?screen='.$data['screen'])->with('notice', 'Guardamos tu feedback. Gracias por ayudarnos a mejorar la beta. No se envían correos automáticos.');
    }
}
