@extends('layouts.bootstrap')

@section('title', __('Register'))

@section('content')

    <form class="needs-validation" method="POST" action="{{ route('register') }}" novalidate>
        @csrf

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
            <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Nombre Apellido" required autofocus />
            <label for="name">{{ __('Name') }}</label>
            <div class="invalid-feedback">
                Por favor ingrese su {{ __('Name') }}.
            </div>
        </div>

        <div class="form-floating mb-3 has-validation">
            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="name@example.com" required />
            <label for="email">{{ __('Email') }}</label>
            <div class="invalid-feedback">
                Por favor ingrese su {{ __('Email') }}.
            </div>
        </div>

        <div class="form-floating mb-3 has-validation">
            <input id="password" type="password" class="form-control" name="password" placeholder="Password" required>
            <label for="password">{{ __('Password') }}</label>
            <div class="invalid-feedback">
                Por favor ingrese su {{ __('Password') }}.
            </div>
        </div>

        <div class="form-floating mb-3 has-validation">
            <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" placeholder="Password" required>
            <label for="password_confirmation">{{ __('Confirm Password') }}</label>
            <div class="invalid-feedback">
                Por favor {{ __('Confirm Password') }}.
            </div>
        </div>

        <div class="text-center pt-1 d-grid gap-2">
            <button type="submit" class="btn shadow text-white btn-block fa-lg gradient-custom-2 mb-3">{{ __('Register') }}</button>
            <a class="text-muted" href="{{ route('login') }}" onclick="verCargandoAuth(this)">{{ __('Already registered?') }}</a>
        </div>

        <div class="position-absolute top-50 start-50 translate-middle d-none verCargando">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

    </form>

@endsection

