@extends('layouts.bootstrap')

@section('title', __('Forgot your password?'))

@section('content')
    <form class="needs-validation" method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <div class="mb-4">
            <p class="fs-6 d-flex" style="text-align: justify !important;">
                <small class="text-muted">{{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}</small>
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
                <div class="fs-6 text-danger fw-normal">{{ __('Whoops! Something went wrong.') }}</div>

                <ul class="mt-3 fs-6 text-danger fw-normal">
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
            <button type="submit" class="btn shadow text-white btn-block  gradient-custom-2">{{ __('Email Password Reset Link') }}</button>
        </div>

        <div class="position-absolute top-50 start-50 translate-middle d-none verCargando">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

    </form>

@endsection
