<?php

namespace App\Providers;

use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //Customizing the asset URL Livewire's
        if (env('APP_ASSET_LIVEWIRE', false)){
            Livewire::setUpdateRoute(function ($handle) {
                return Route::post('/'.env('APP_ASSET_LIVEWIRE', '').'/livewire/update', $handle)->name('assetlivewire.update');
            });

            Livewire::setScriptRoute(function ($handle) {
                return Route::get('/'.env('APP_ASSET_LIVEWIRE', '').'/livewire/livewire.js', $handle);
            });
        }
        //Políticas de roles y permisos
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
    }
}
