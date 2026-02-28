document.addEventListener('DOMContentLoaded', function () {

    //mostrar manuealmente el preloader
    window.mostrarPreloader = function () {
        const preloader = document.getElementById('preloader');
        if (preloader){
            preloader.classList.remove('d-none');
        }
    }

    // Inicialización global
    console.log('✅ web-app.js');
});
