<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class LoginViaSSO
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            $ssoTokenUrl = 'http://127.0.0.1:8000/api/sso-token?email=' . urlencode($request->cookie('sso_email'));
    
            try {
                $response = Http::get($ssoTokenUrl);
                if ($response->ok() && $response->json('valid')) {
                    $user = User::where('email', $response->json('email'))->first();
                    if ($user) {
                        Auth::login($user);
                    }
                }
            } catch (\Exception $e) {
                // optionally log
            }
        }
    
        return $next($request);
    }
}
