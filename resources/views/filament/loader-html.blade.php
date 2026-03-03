<div>

    <!-- loader -->
    <div id="ftco-loader" class="fullscreen">
        <svg class="circular" width="48px" height="48px">
            <circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/>
            <circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#1977cc"/>
        </svg>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('loader', {
                show() {
                    document.getElementById('ftco-loader')?.classList.add('show');
                },
                hide() {
                    document.getElementById('ftco-loader')?.classList.remove('show');
                }
            });

            Livewire.hook('message.sent', () => Alpine.store('loader').show());
            Livewire.hook('message.processed', () => Alpine.store('loader').hide());

            document.addEventListener('click', (e) => {
                const link = e.target.closest('a');
                if (!link) return;

                const isSidebar = link.closest('aside');
                const isInternal = link.href?.startsWith(window.location.origin);
                const isMenuLink = link.classList.contains('menu-link');

                if ((isSidebar || isMenuLink) && isInternal) {
                    Alpine.store('loader').show();
                }

            });
        });
    </script>

</div>
