const CACHE_NAME = 'sice-peñalver-v1';

const assetsToCache = [
  './index.html',
  './assets/bootstrap/css/bootstrap.min.css',
  './assets/bootstrap/js/bootstrap.bundle.min.js',
  './controllers/login/index.js',
  './assets/img/icon-192.png',
  './assets/img/icon-512.png'
];

self.addEventListener('install', (e) => {
  e.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return Promise.all(
        assetsToCache.map((url) => {
          return cache.add(url).catch((err) => {
            console.error(`❌ Falló la descarga de este archivo en el SW: ${url}`, err);
          });
        })
      );
    })
  );
});

self.addEventListener('fetch', (e) => {
  e.respondWith(
    caches.match(e.request).then((res) => {
      return res || fetch(e.request);
    })
  );
});