<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/sso-login', function (\Illuminate\Http\Request $request) {
    if (! $request->hasValidSignature()) {
        abort(403, 'Invalid or expired signature.');
    }

    $user = User::where('email', $request->email)->first();

    // If user doesn't exist, fetch from appA via secure API
    if (! $user) {
        $user = \App\Models\User::firstOrCreate(
            ['email' => $request->email],
            [
                'name' => $request->name ?? 'SSO User',
                'password' => Hash::make(Str::random(16))
            ]
        );
    }

    Auth::login($user);
    return redirect()->route('home');
})->name('foodpanda.sso-login');

Route::get('/sso-logout', function (Request $request) {
    $expectedSig = hash_hmac('sha256', $request->email, env('APP_KEY'));

    if ($request->signature !== $expectedSig) {
        abort(403);
    }

    if (Auth::check() && Auth::user()->email === $request->email) {
        Auth::logout();
    }

    return response('ok');
});