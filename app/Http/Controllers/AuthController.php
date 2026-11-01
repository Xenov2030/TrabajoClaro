<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\PilotRequest;
use App\Models\User;
use App\Services\Demo;
use App\Services\Workflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $r, Workflow $flow)
    {
        $r->merge(['email' => mb_strtolower(trim((string) $r->input('email', '')))]);
        $data = $r->validate(['name' => 'required|string|max:120', 'email' => 'required|email|max:190|unique:users', 'password' => ['required', 'confirmed', Password::min(10)], 'business_name' => 'required|string|max:120', 'industry' => 'required|string|max:100', 'country' => 'required|in:AR,UY,CL,BO,BR,PY,PE,CO,MX,ES,US', 'timezone' => 'required|timezone', 'currency' => 'required|in:ARS,USD']);
        $u = DB::transaction(function () use ($data, $flow) {
            $u = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password'])]);
            $b = Business::create(['name' => $data['business_name'], 'industry' => $data['industry'], 'country' => $data['country'], 'timezone' => $data['timezone'], 'currency' => $data['currency']]);
            BusinessMembership::create(['business_id' => $b->id, 'user_id' => $u->id]);
            $flow->metric($b->id, 'registration_completed');

            return $u;
        });
        Auth::login($u);
        $r->session()->regenerate();

        return redirect('/app');
    }

    public function login(Request $r)
    {
        $r->merge(['email' => mb_strtolower(trim((string) $r->input('email', '')))]);
        $data = $r->validate(['email' => 'required|email', 'password' => 'required|string']);
        if (! Auth::attempt($data)) {
            throw ValidationException::withMessages(['email' => 'El correo o la contraseña no coinciden.']);
        }
        $r->session()->regenerate();

        return redirect()->intended('/app');
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return redirect('/');
    }

    public function demo(Request $r, Demo $demo)
    {
        $u = $demo->create();
        Auth::login($u);
        $r->session()->regenerate();

        return redirect('/app')->with('notice', 'Demo privada con datos ficticios. Tus cambios no afectan a otros visitantes. Vence en 24 horas.');
    }

    public function pilot(Request $r)
    {
        $data = $r->validate(['name' => 'required|string|max:120', 'email' => 'required|email|max:190', 'message' => 'required|string|min:15|max:2000', 'website' => 'nullable|max:0']);
        PilotRequest::create(collect($data)->only(['name', 'email', 'message'])->all());

        return redirect('/#piloto')->with('notice', 'Guardamos tu solicitud de piloto. Este formulario no envía correos automáticos.');
    }
}
