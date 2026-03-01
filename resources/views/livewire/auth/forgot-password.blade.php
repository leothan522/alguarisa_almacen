@extends('layouts.bootstrap')

@section('title', __('Forgot your password?'))

@section('content')
    <form class="needs-validation mb-3 mb-sm-auto" method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <div class="mb-4">
            <p class="fs-6 d-flex" style="text-align: justify !important;">
                <small class="text-muted">{{ __('Enter your email to receive a password reset link') }}</small>
            </p>
        </div>

        @if (session('status'))
            <div class="mb-4">
                <p class="fs-6 d-flex text-success fw-normal" style="text-align: justify !important;">
                    <small>{{ session('status') }}</small>
                </p>
            </div>
        @endif

        @if ($errors->any())
            <div>
                <ul class="fs-6 text-danger fw-normal">
                    @foreach ($errors->all() as $error)
                        <li><small>{{ $error }}</small></li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="form-floating mb-3 has-validation">
            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}"
                   placeholder="name@example.com" required autofocus/>
            <label for="email">{{ __('Email') }}</label>
            <div class="invalid-feedback">
                Por favor ingrese su {{ __('Email') }}.
            </div>
        </div>

        <div class="text-center pt-1 pb-1 d-grid gap-2">
            <button type="submit" class="btn shadow text-white btn-block  gradient-custom-2">{{ __('Email password reset link') }}</button>
        </div>

        <div x-data class="d-flex align-items-center justify-content-center mt-4">
            <p class="mb-0 me-2">{{ __('Or, return to') }}</p>
            <a href="{{ route('login') }}" class="text-muted" @click="mostrarPreloader()">{{ __('log in') }}</a>
        </div>

    </form>

@endsection
