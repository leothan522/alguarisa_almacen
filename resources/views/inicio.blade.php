@extends('layouts.bootstrap')

@section('title', __('Welcome'))

@section('content')

    <div x-data class="text-center mb-5 mb-sm-auto">
        @auth
            <a class="text-muted" href="{{ route('profile.edit') }}" @click="mostrarPreloader()">{{ __('Settings') }}</a>
            <a class="text-muted ms-3" href="{{ route('filament.dashboard.pages.dashboard') }}" @click="mostrarPreloader()">Dashboard</a>
        @else
            <a class="text-muted" href="{{ route('filament.dashboard.pages.dashboard') }}" @click="mostrarPreloader()">{{ __('Log in') }}</a>
            @if (Route::has('register'))
                <a class="text-muted ms-3" href="{{ route('register') }}" @click="mostrarPreloader()">{{ __('Register') }}</a>
            @endif
            <a class="text-muted ms-3 text-nowrap" href="{{ route('instalar-app') }}" @click="mostrarPreloader">Instalar App</a>
        @endauth
    </div>

@endsection
