const version = "v1.0.29" + new Date().getTime();
var staticCacheName = "pwa-v-sto-online" + version;
var filesToCache = [
    "/offline",
    "/css/app.css",
    "/js/app.js",
    "/images/icons/icon-72x72.png",
    "/images/icons/icon-96x96.png",
    "/images/icons/icon-128x128.png",
    "/images/icons/icon-144x144.png",
    "/images/icons/icon-152x152.png",
    "/images/icons/icon-192x192.png",
    "/images/icons/icon-384x384.png",
    "/images/icons/icon-512x512.png",
    "/images/STO ONLINE.png",
    "/images/user2-160x160.jpg",
    "/images/avatar.png",
    "/images/BEV-LOGO.png",
    "/images/pexels-apasaric-2603464.jpg",
    "/images/pexels-hngstrm-2341290.jpg",
    "/images/pexels-hngstrm-inverted.jpg",
    "/images/BEVI-ARANGA-SKETCH.png",
    "/vendor/adminlte/dist/css/adminlte.min.css",
    "/vendor/dropzone/min/dropzone.min.js",
    "/vendor/jquery/jquery.min.js",
    "/vendor/bootstrap/js/bootstrap.bundle.min.js",
    "/vendor/fontawesome-free/webfonts/fa-solid-900.woff2",
    "/vendor/select2/js/select2.min.js",
    "/vendor/fontawesome-free/css/all.min.css",
    "/vendor/adminlte/dist/js/adminlte.min.js",
    "/vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js",
    "/vendor/overlayScrollbars/css/OverlayScrollbars.min.css",
    "/vendor/select2/css/select2.min.css",
    "/vendor/dropzone/min/dropzone.min.css",
    "/css/admin_custom.css",
    "vendor/apexcharts/dist/apexcharts.min.js",
    "vendor/highcharts/highcharts.js",
    "vendor/highcharts/modules/data.js",
    "vendor/highcharts/modules/drilldown.js",
    "vendor/highcharts/modules/exporting.js",
    "vendor/highcharts/modules/export-data.js",
    "vendor/highcharts/modules/accessibility.js",
    "vendor/highcharts/modules/map.js",
    "vendor/sweetalert2/sweetalert2.all.min.js",
    "vendor/sweetalert2/sweetalert2.min.css",
    "rappasoft/laravel-livewire-tables/thirdparty.css",
    "rappasoft/laravel-livewire-tables/thirdparty.min.js",
    "rappasoft/laravel-livewire-tables/core.min.js",
    "rappasoft/laravel-livewire-tables/core.min.css",
];

// Cache on install
self.addEventListener("install", (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName).then((cache) => {
            return Promise.all(
                filesToCache.map((url) => {
                    return cache.add(url).catch((err) => {
                        console.error("Failed to cache file:", url, err);
                    });
                }),
            );
        }),
    );
});

// Clear cache on activate
self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((cacheName) =>
                        cacheName.startsWith("pwa-v-sto-online"),
                    )
                    .filter((cacheName) => cacheName !== staticCacheName)
                    .map((cacheName) => caches.delete(cacheName)),
            );
        }),
    );
});

// Serve from Cache
self.addEventListener("fetch", (event) => {
    event.respondWith(
        caches
            .match(event.request)
            .then((response) => {
                return response || fetch(event.request);
            })
            .catch(() => {
                return caches.match("offline");
            }),
    );
});
