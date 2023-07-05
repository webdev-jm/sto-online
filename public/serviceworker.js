var staticCacheName = "pwa-v-sto-online" + new Date().getTime();
var filesToCache = [
    '/offline',
    '/css/app.css',
    '/js/app.js',
    '/images/icons/icon-72x72.png',
    '/images/icons/icon-96x96.png',
    '/images/icons/icon-128x128.png',
    '/images/icons/icon-144x144.png',
    '/images/icons/icon-152x152.png',
    '/images/icons/icon-192x192.png',
    '/images/icons/icon-384x384.png',
    '/images/icons/icon-512x512.png',
    '/images/STO ONLINE.png',
    '/images/user2-160x160.jpg',
    '/images/avatar.png',
    '/images/LOGO.jpg',
    '/images/pexels-gdtography-911738.jpg',
    '/vendor/adminlte/dist/css/adminlte.min.css',
    '/vendor/dropzone/min/dropzone.min.js',
    '/vendor/jquery/jquery.min.js',
    '/vendor/bootstrap/js/bootstrap.bundle.min.js',
    '/vendor/fontawesome-free/webfonts/fa-solid-900.woff2',
    '/vendor/select2/js/select2.min.js',
    '/vendor/fontawesome-free/css/all.min.css',
    '/vendor/adminlte/dist/js/adminlte.min.js',
    '/vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js',
    '/vendor/overlayScrollbars/css/OverlayScrollbars.min.css',
    '/vendor/select2/css/select2.min.css',
    '/vendor/dropzone/min/dropzone.min.css',
    '/css/admin_custom.css',
];

// Cache on install
self.addEventListener("install", event => {
    this.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName)
            .then(cache => {
                return cache.addAll(filesToCache);
            })
    )
});

// Clear cache on activate
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(cacheName => (cacheName.startsWith("pwa-v-sto-online")))
                    .filter(cacheName => (cacheName !== staticCacheName))
                    .map(cacheName => caches.delete(cacheName))
            );
        })
    );
});

// Serve from Cache
self.addEventListener("fetch", event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                return response || fetch(event.request);
            })
            .catch(() => {
                return caches.match('offline');
            })
    )
});