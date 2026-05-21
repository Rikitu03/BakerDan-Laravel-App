<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    private function expectsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->is('api/*');
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            if ($this->expectsJson($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return redirect('/login');
        }

        if (Auth::user()->role !== $role) {
            if ($this->expectsJson($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to access this resource.',
                ], 403);
            }

            if (Auth::user()->role === 'admin') {
                return redirect('/admin');
            }
            return redirect('/');
        }

        return $next($request);
    }
}
