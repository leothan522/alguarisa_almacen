@extends('layouts.bootstrap')

@section('title', __('Log in'))

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush

@section('content')
    <form class="needs-validation mb-3 mb-sm-auto" method="POST" action="{{ route('login.store') }}" novalidate>
        @csrf

        @if ($errors->any())
            <div>
                <ul class="fs-6 text-danger fw-normal">
                    @foreach ($errors->all() as $error)
                        <li><small>{{ $error }}</small></li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('status'))
            <div class="mb-4">
                <p class="fs-6 d-flex text-success fw-normal" style="text-align: justify !important;">
                    <small>{{ session('status') }}</small>
                </p>
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

        <div class="form-floating mb-3 has-validation position-relative">
            <input id="password" type="password" class="form-control" name="password" placeholder="Password" required>
            <label for="password">{{ __('Password') }}</label>
            <div class="invalid-feedback">
                Por favor ingrese su {{ __('Password') }}.
            </div>

            <!-- Botón para mostrar/ocultar -->
            <button type="button" class="btn btn-sm btn-outline-secondary position-absolute top-50 end-0 translate-middle-y me-2" onclick="togglePassword()">
                <i id="toggleIcon" class="bi bi-eye"></i>
            </button>
        </div>


        <div class="mb-3 ms-1 has-validation">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="on" name="remember" id="remember_me">
                <label class="form-check-label" for="remember_me">
                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>
        </div>

        <div x-data class="text-center pt-1 mb-3 pb-1 d-grid gap-2">

            <button type="submit" class="btn shadow text-white btn-block fa-lg gradient-custom-2 mb-3">{{ __('Log in') }}</button>

            @if (Route::has('password.request'))
                <a class="text-muted" href="{{ route('password.request') }}" @click="mostrarPreloader()">{{ __('Forgot your password?') }}</a>
            @endif
        </div>

        @if (Route::has('register'))
            <div x-data class="d-flex align-items-center justify-content-center">
                <p class="mb-0 me-2">{{ __('Don\'t have an account?') }}</p>
                <a href="{{ route('register') }}" class="btn btn-outline-primary btn-sm" @click="mostrarPreloader()">{{ __('Sign up') }}</a>
            </div>
        @endif

    </form>
@endsection

@push('js')
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }
    </script>
@endpush
