<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Models\SignalBit\UserPassword;
use Illuminate\Support\Facades\URL;

class LoginController extends Controller
{
    public function index() {
        return view("auth.login");
    }

    /**
     * Handle an authentication attempt.
     *
     * @param  \App\Http\Requests\LoginRequest $request
     *
     * @return Response
     */
    public function authenticate(LoginRequest $request)
    {
        $credentials = $request->validated();

        $remember = isset($credentials['remember']) && $credentials['remember'] == "true" ? true : false;

        $userData = UserPassword::select('Groupp')->where('username', $credentials['username'])->where('password', $credentials['password'])->first();

        if ($userData->Groupp == 'SEWING' || $userData->Groupp == 'ALLSEWING') {
            if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']], $remember)) {
                $request->session()->regenerate();

                session(['user_id' => Auth::user()->line_id, 'user_username' => Auth::user()->username, 'user_name' => Auth::user()->FullName]);

                return array(
                    'status' => '200',
                    'message' => 'Authenticate Success',
                    'redirect' => url('/'),
                    'additional' => [],
                );
            }
        }

        return array(
            'status' => '400',
            'message' => 'Username atau Password salah',
            'redirect' => '',
            'additional' => ['username', 'password']
        );
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function unauthenticate(Request $request)
    {
        if ($request->confirmed) {
            Auth::logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            return array(
                "status" => 200,
                "message" => "Unauthenticate Success",
                "redirect" => url('/login')
            );
        }
    }
}
