<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SweetAlert2\Laravel\Swal;
use Symfony\Component\HttpFoundation\Response;

class UserActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            if (! Auth::user()->is_active && ! Auth::user()->is_root) {
                Auth::guard('web')->logout();
                session()->flush();
                Swal::toastInfo([
                    'text' => 'Usuario Inactivo',
                    'showCloseButton' => true,
                    'showConfirmButton' => false,
                    'position' => 'top',
                    'timer' => 5000,
                    'timerProgressBar' => true,
                ]);
                return redirect()->route('home');
            }
        }

        return $next($request);
    }
}
