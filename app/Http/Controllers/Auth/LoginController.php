<?php

namespace App\Http\Controllers\Auth;

use Auth;
use App\Models\Auth\User;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Socialite;
use Conduit\Conduit;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login()
    {
        try {
            // Connect to social driver and authenticate
            return Socialite::driver('eveonline')
                ->scopes(['publicData',
                    'esi-industry.read_character_mining.v1',
                    'esi-industry.read_corporation_mining.v1'])
                ->redirect();
            return Socialite::driver('eveonline')->redirect();
        } catch (\Exception $e) {
            Log::error('Redirect to EvE Online SSO failed');
            return abort(502);
        }
    }

    public function callback()
    {
        try {
            $ssoUser = Socialite::driver('eveonline')->user();
        } catch (InvalidStateException $e) {
            return redirect()->route('login');
        }

        // Get/Create User
        $user = User::firstOrNew(['character_id' => $ssoUser->id]);

        // New user?
        if (!$user->exists) {

            // Connect to API
            $auth = new \Conduit\Authentication(env('EVEONLINE_CLIENT_ID'), env('EVEONLINE_CLIENT_SECRET'), $ssoUser->refreshToken);
            $api = new Conduit($auth);

            // Get & save Character details
            $apiCharacter = $api->characters($ssoUser->id)->get();

            $user->character_id = $ssoUser->id;
            $user->character_name = $apiCharacter->name;
            $user->refresh_token = $ssoUser->refreshToken;
            $user->corporation_id = $apiCharacter->corporation_id;
            if (isset($apiCharacter->data->alliance_id)) {
                $user->alliance_id = $apiCharacter->alliance_id;
            }
            $user->save();
        }

        // and then log in
        Auth::login($user, true);

        return redirect('/dashboard');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}
