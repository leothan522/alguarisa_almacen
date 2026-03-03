<div style="position: fixed; bottom: 0; left: 0; width: 100vw; z-index: 9999; pointer-events: none; isolation: isolate;">
    <!-- Franja de ancho completo -->
    <div class="shadow-2xl"
         style="pointer-events: auto; width: 100%; background-color: #ffffff; border-top: 1px solid #e5e7eb; display: flex; justify-content: center; align-items: center;"
         data-footer-container>

        <style>
            /* Forzamos colores sólidos para evitar la transparencia en modo oscuro */
            .dark [data-footer-container] {
                background-color: #030712 !important;
                border-top: 1px solid #1f2937 !important;
            }
        </style>

        <!-- Contenedor con Flex para centrado absoluto -->
        <div style="width: 100%; display: flex; justify-content: center; align-items: center; min-height: 32px;" class="py-2">
            <p style="margin: 0; padding: 0; text-align: center; width: 100%;" class="text-xs font-bold tracking-widest uppercase">
                <small class="text-gray-500 dark:text-gray-400 font-medium normal-case">Desarrollado por</small>
                <small class="ml-1 text-primary-600 dark:text-primary-400">
                    Ing. Yonathan Castillo
                </small>
            </p>
        </div>
    </div>
</div>


