@extends('layouts.bootstrap')

@section('title', 'Verificar correo electrónico')

@section('content')

    <form class="needs-validation" method="POST" action="{{ route('verification.send') }}" novalidate>
        @csrf

        <div class="mb-4">
            <p class="fs-6 d-flex" style="text-align: justify !important;">
                <small class="text-muted">{{ __('Before continuing, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}</small>
            </p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4">
                <p class="fs-6 d-flex text-success fw-normal" style="text-align: justify !important;">
                    <small>{{ __('A new verification link has been sent to the email address you provided in your profile settings.') }}</small>
                </p>
            </div>
        @endif

        <div class="text-center pt-1 mb-3 pb-1 d-grid gap-2">
            <button type="submit" class="btn shadow text-white btn-block  gradient-custom-2 mb-3">{{ __('Resend Verification Email') }}</button>
        </div>

        <div class="position-absolute top-50 start-50 translate-middle d-none verCargando">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

    </form>

    <div class="d-flex align-items-center justify-content-between">
        <a class="text-muted mb-0 me-2" href="{{ route('profile.show') }}" onclick="verCargandoAuth(this)">{{ __('Edit Profile') }}</a>
        <form method="POST" action="{{ route('logout') }}" onsubmit="verCargandoAuth()">
            @csrf
            <button type="submit" class="btn btn-link text-muted">{{ __('Log Out') }}</button>
        </form>
    </div>

@endsection

