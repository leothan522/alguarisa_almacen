@extends('layouts.bootstrap')

@section('title', __('Reset Password'))

@section('content')
    <form class="needs-validation" method="POST" action="{{ route('password.update') }}" novalidate>
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

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
            <input id="email" type="email" class="form-control" name="email" value="{{ old('email', $request->email) }}" placeholder="name@example.com" required autofocus/>
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

        <div class="text-center pt-1 pb-1 d-grid gap-2">
            <button type="submit" class="btn shadow text-white btn-block fa-lg gradient-custom-2">{{ __('Reset Password') }}</button>
        </div>

        <div class="position-absolute top-50 start-50 translate-middle d-none verCargando">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

    </form>

@endsection
