<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SweetAlert2\Laravel\Swal;
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
        if (Auth::check()) {
            if (! isAdmin() && ! Auth::user()->access_panel) {
                Swal::toastInfo([
                    'text' => 'No tienes acceso al Dashboard',
                    'showCloseButton' => true,
                    'showConfirmButton' => false,
                    'position' => 'top',
                    'timer' => 3000,
                    'timerProgressBar' => true,
                ]);

                return redirect()->route('home');
            }
        }

        return $next($request);
    }
}
