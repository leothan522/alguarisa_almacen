const CACHE_NAME = 'alimentosdelguaricosa-cache-v1';

const PRECACHE_URLS = [
    './favicons/offline.html'
];

// Precaching en instalación
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(PRECACHE_URLS))
    );
    self.skipWaiting();
});

// Activación y limpieza de caches antiguos
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
            )
        )
    );
    self.clients.claim();
});

// Intercepta peticiones y responde desde cache si es posible
self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;

    event.respondWith(
        fetch(event.request).catch(() =>
            caches.match(event.request).then(response => {
                if (response) return response;
                if (event.request.mode === 'navigate') {
                    return caches.match('./favicons/offline.html');
                }
            })
        )
    );
});
