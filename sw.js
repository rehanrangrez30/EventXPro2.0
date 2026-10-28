/**
 * EventX Pro - Service Worker
 * Caches static assets for faster repeat loads and shows a friendly
 * offline page when navigation requests fail with no network.
 * Dynamic/database-backed pages are always fetched fresh (network-first)
 * so registrations, dashboards, etc. never show stale data.
 */
const CACHE_NAME = 'eventx-pro-static-v1';
const OFFLINE_URL = 'offline.html';

const STATIC_ASSETS = [
    'assets/css/style.css',
    'assets/js/main.js',
    'assets/js/audio-player.js',
    'assets/images/icons/icon-192.png',
    'assets/images/icons/icon-512.png',
    OFFLINE_URL,
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Only handle GET requests; let POST (forms, registration, login) pass through untouched.
    if (request.method !== 'GET') {
        return;
    }

    // Page navigations: try the network first (dynamic PHP content), fall back to offline page.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // Static assets: cache-first for speed, network fallback if not cached yet.
    const isStaticAsset = STATIC_ASSETS.some((asset) => request.url.endsWith(asset));
    if (isStaticAsset) {
        event.respondWith(
            caches.match(request).then((cached) => cached || fetch(request))
        );
    }
});
