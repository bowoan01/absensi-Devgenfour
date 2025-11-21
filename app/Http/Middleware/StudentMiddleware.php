<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StudentMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user || !$user->isStudent()) {
            abort(403, 'Unauthorized');
        }
        if ($user->status !== \App\Models\User::STATUS_ACTIVE) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Account inactive.']);
        }
        return $next($request);
    }
}
