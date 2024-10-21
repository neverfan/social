<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use App\Support\Auth\JwtToken;
use App\Exceptions\Auth\UnauthorizedException;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $decoded = JwtToken::decode($request->bearerToken());

        if ($decoded->expires_in < time()) {
            throw new UnauthorizedException();
        }

        $user = User::query()->where('id', $decoded->user_id)->firstOrFail();

        Auth::setUser($user);

        return $next($request);
    }
}
