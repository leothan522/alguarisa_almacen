@extends('layouts.bootstrap')

@section('content')
    <div class="text-center pt-1 mb-5 pb-1 position-relative">
        @auth
            <a class="text-muted" href="{{ route('profile.show') }}" onclick="verCargandoAuth(this)">{{ __('Profile') }}</a>
            <a class="text-muted ms-3" href="{{ url('/dashboard') }}" onclick="verCargandoAuth(this)">Dashboard</a>
        @else
            <a class="text-muted" href="{{ route('login') }}" onclick="verCargandoAuth(this)">{{ __('Log in') }}</a>
            @if (Route::has('register'))
                <a class="text-muted ms-3" href="{{ route('register') }}" onclick="verCargandoAuth(this)">{{ __('Register') }}</a>
            @endif
        @endauth
        <div class="position-absolute top-50 start-50 translate-middle d-none verCargando">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
@endsection
