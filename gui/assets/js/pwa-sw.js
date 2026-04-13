//const showOffline = caches.open('pwa-cache').then(cache => cache.match(offlineFallbackPage));
self.addEventListener('install', e => self.skipWaiting());
self.addEventListener('activate', e => e.waitUntil(clients.claim()));
self.addEventListener('fetch', e => {
    if (e.request.mode === 'navigate') {
        const response = e.preloadResponse.then(preloadResp => preloadResp || fetch(e.request)).catch(() => null);
        e.respondWith(response);
    }
});