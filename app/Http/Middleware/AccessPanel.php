<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AccessPanel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()){
            if (!Auth::user()->access_panel && !Auth::user()->is_root){
                sweetAlert2([
                    'icon' => 'info',
                    'text' => 'No tienes acceso al Dashboard',
                    'timer' => 3000,
                ]);
                return redirect()->route('home');
            }
        }
        return $next($request);
    }
}
