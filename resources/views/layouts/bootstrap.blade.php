<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'ALGUARISA') - {{ config('app.name', 'Laravel') }}</title>

    <meta name="description" content="Plataforma de organización de Alimentos del Guárico S.A.">
    <meta name="theme-color" content="#ffffff">

    <meta property="og:title" content="ALGUARISA">
    <meta property="og:description" content="Plataforma de organización de Alimentos del Guárico S.A.">
    <meta property="og:image" content="{{ asset('favicons/favicon-128x128.png') }}">

    {{-- Favicon y PWA --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/favicon-32x32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicons/favicon-128x128.png') }}">

    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    <!--Bootstrap -->
    {{--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">--}}
    @vite(['resources/js/bootstrap5.js', 'resources/js/sweetalert2.js', 'resources/js/web-app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;400&display=swap" rel="stylesheet">

    <style>

        * {
            font-family: "Poppins", sans-serif;
            font-weight: 400;
            font-style: normal;
        }

        .text_title {
            color: rgba(8, 23, 44, 1);
            font-weight: bold;
        }

        .gradient-custom-2 {
            /* fallback for old browsers */
            background: rgb(18, 58, 108);

            /* Chrome 10-25, Safari 5.1-6 */
            background: -webkit-radial-gradient(circle, rgba(18, 58, 108, 1) 0%, rgba(8, 23, 44, 1) 100%);

            /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
            background: radial-gradient(circle, rgba(18, 58, 108, 1) 0%, rgba(8, 23, 44, 1) 100%);
        }

        @media (min-width: 768px) {
            .gradient-form {
                height: 100vh !important;
            }
        }

        @media (min-width: 769px) {
            .gradient-custom-2 {
                border-top-right-radius: .3rem;
                border-bottom-right-radius: .3rem;
            }
        }

        @media (max-height: 650px){
            #scale {
                transform: scale(0.95); /* Reduce el tamaño al 95% */
            }
        }

        /*--------------------------------------------------------------
        # Preloader
        --------------------------------------------------------------*/
        #preloader {
            position: fixed;
            inset: 0;
            z-index: 999999;
            overflow: hidden;
            background: #ffffff;
            transition: all 0.6s ease-out;
        }

        #preloader:before {
            content: "";
            position: fixed;
            top: calc(50% - 30px);
            left: calc(50% - 30px);
            border: 6px solid #ffffff;
            border-color: #1977cc transparent #1977cc transparent;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: animate-preloader 1.5s linear infinite;
        }

        @keyframes animate-preloader {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /*--------------------------------------------------------------
        # Imagenes Extras
        --------------------------------------------------------------*/

        .gobernacion {
            display: block;
            position: absolute;
            height: 100px;
            width: 100px;
            right: 3%;
            top: 3%;
        }

        .gobernacion_start {
            display: block;
            position: absolute;
            height: 100px;
            width: 100px;
            left: 3%;
            top: 7%;
        }


    </style>

    @stack('css')
    @livewireStyles
</head>
<body style="background-color: #eee;">

<div class="gradient-form" style="min-height: 100vh;">
    <div class="position-absolute top-50 start-50 translate-middle container">


        <div id="scale" class="row d-flex justify-content-center align-items-center">
            <div class="col-xl-10">
                <div class="card rounded-3 text-black">
                    <div class="row g-0">
                        <div class="col-lg-6 d-flex align-items-center">
                            <div class="card-body p-md-5 mx-md-4" id="card_body">

                                <img class="gobernacion_start d-lg-none" src="{{ asset('img/logo_gobernacion.png') }}" alt="Logo Gobernación Guárico">

                                <div class="row d-sm-block d-md-none" style="min-height: 50px;">&nbsp;</div>

                                <div class="text-center">
                                    <a x-data href="{{ route('home') }}">
                                        <img class="img-fluid" src="{{ asset('img/logo_alguarisa.png') }}" alt="Logo AlGUARISA" @click="mostrarPreloader()">
                                    </a>
                                    <h6 class="mt-1 mb-4 pb-1 text_title"><strong>Dirección de Tecnología y Sistemas.</strong></h6>
                                </div>

                                @yield('content')

                            </div>
                        </div>
                        <div class="col-lg-6 d-none d-lg-flex align-items-center gradient-custom-2"
                             style="min-height: 70vh">
                            <img class="gobernacion" src="{{ asset('img/logo_gobernacion_white.png') }}" alt="Logo Gobernación Guárico">
                            <div class="text-white px-3 py-4 p-md-5 mx-md-4">
                                <img class="img-fluid rounded-2 border border-light" src="{{ asset('img/logo_tecnologia.png') }}" alt="Logo Tecnología Alguarisa">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
</div>

<!-- Footer Sticky -->
<footer class="text-center py-2 bg-light border-top fixed-bottom" style="z-index: 1;">
    <small class="text-muted">Desarrollado por Ing. Yonathan Castillo</small>
</footer>

<!-- Preloader -->
<div id="preloader"></div>

{{--<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>--}}
<script type="application/javascript">

    //Script para ocultar el preloader
    window.addEventListener('load', function () {
        document.querySelector('#preloader').classList.add('d-none');
    });

    //Validar Formularios
    (() => {
        'use strict'
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        const forms = document.querySelectorAll('.needs-validation')

        // Loop over them and prevent submission
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                } else {
                    mostrarPreloader();
                }
                form.classList.add('was-validated');
            }, false);
        })
    })();
</script>

@stack('js')
@livewireScripts
@include('sweetalert2::index')
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register("{{ asset('service-worker.js') }}")
                .then(reg => console.log('✅ Service Worker registrado en:', reg.scope))
                .catch(err => console.error('⚠️ Error al registrar el Service Worker:', err));
        });
    }
</script>
</body>
</html>
