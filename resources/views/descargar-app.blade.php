@extends('layouts.bootstrap')

@section('title', 'Descargar APP')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
@endpush

@section('content')

    <div class="row mb-2 justify-content-center text-center">

        <h6 class="mt-5 mt-md-auto pb-1 text-center">
            <strong>Instalar App</strong>
        </h6>

        <div x-data class="col-6 text-center">
            <a href="{{ route('home') }}" @click="mostrarPreloader">
                <img class="img-fluid" src="{{ asset('img/logo.png') }}" alt="Logo de ALGUARISA" />
            </a>
        </div>

        <div x-data class="col-6 text-center">
            <img class="img-fluid" src="{{ $qrIos }}" alt="Codigo QR" />
            <a class="text-decoration-none" href="{{ route('home') }}" @click="mostrarPreloader">
                <i class="bi bi-arrow-up-right-square"></i> Abrir
            </a>
        </div>

        <div class="mt-4">
            <p class="fs-6 d-flex" style="text-align: justify !important;">
                <small class="text-muted">
                    En <strong>Android</strong> abre el enlace del código QR en Google Chrome. Toca “Agregar a la pantalla principal” en la barra de direcciones o menú. Se instala la app y listo.
                </small>
            </p>
        </div>

        <div class="mt-3">
            <p class="fs-6 d-flex" style="text-align: justify !important;">
                <small class="text-muted">
                    En <strong>iPhone</strong> desde Safari: Abre el enlace del código QR en Safari. Toca “Compartir” → “Agregar a pantalla de inicio”. Se instala la app y listo.
                </small>
            </p>
        </div>
    </div>

    <div x-data class="text-center pt-1 mb-5 pb-1">
        @auth
            <a class="text-muted" href="{{ route('profile.edit') }}" @click="mostrarPreloader">{{ __('Profile') }}</a>
            <a class="text-muted ms-3" href="{{ url('/dashboard') }}" @click="mostrarPreloader">{{ __('Dashboard') }}</a>
        @else
            <a class="text-muted" href="{{ route('login') }}" @click="mostrarPreloader">{{ __('Log in') }}</a>
            @if (Route::has('register'))
                <a class="text-muted ms-3" href="{{ route('register') }}" @click="mostrarPreloader">{{ __('Register') }}</a>
            @endif
        @endauth
    </div>

@endsection
