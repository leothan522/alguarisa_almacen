@extends('layouts.bootstrap')

@section('title', 'Verificar correo electrónico')

@section('content')
    <form class="needs-validation mb-3 mb-sm-auto" method="POST" action="{{ route('verification.send') }}" novalidate>
        @csrf

        <div class="mb-4">
            <p class="fs-6 d-flex" style="text-align: justify !important;">
                <small class="text-muted">Antes de continuar, ¿podría verificar su dirección de correo electrónico haciendo clic en el enlace que le acabamos de enviar? Si no recibió el correo electrónico, con gusto le enviaremos otro.</small>
            </p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4">
                <p class="fs-6 d-flex text-success fw-normal" style="text-align: justify !important;">
                    <small>Se ha enviado un nuevo enlace de verificación a la dirección de correo electrónico que proporcionó en la configuración de su perfil.</small>
                </p>
            </div>
        @endif

        <div class="text-center pt-1 mb-3 pb-1 d-grid gap-2">
            <button type="submit" class="btn shadow text-white btn-block  gradient-custom-2 mb-3">{{ __('Resend verification email') }}</button>
        </div>

    </form>

    <div x-data class="d-flex align-items-center justify-content-between">
        <a class="text-muted mb-0 me-2" href="{{ route('profile.edit') }}" @click="mostrarPreloader()">{{ __('Settings') }}</a>
        <form method="POST" action="{{ route('logout') }}" @submit="mostrarPreloader()">
            @csrf
            <button type="submit" class="btn btn-link text-muted">{{ __('Log out') }}</button>
        </form>
    </div>

@endsection
