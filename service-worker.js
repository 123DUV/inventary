const CACHE_NAME = "inventario-cache-v1";
const urlsToCache = [
  "./",
  "./index.html",
  "./inv.png",
  "./manifest.json",
  "./api.php"
  // 👉 Agrega aquí más archivos si tienes otros (CSS, JS, imágenes, etc.)
];

// Instalar el service worker y guardar archivos en caché
self.addEventListener("install", event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      console.log("Archivos en caché correctamente");
      return cache.addAll(urlsToCache);
    })
  );
});

// Interceptar peticiones y responder desde la caché
self.addEventListener("fetch", event => {
  event.respondWith(
    caches.match(event.request).then(response => {
      // Si está en caché, se devuelve, si no, se descarga
      return response || fetch(event.request);
    })
  );
});

// Actualizar la caché cuando cambie la versión
self.addEventListener("activate", event => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (!cacheWhitelist.includes(cacheName)) {
            console.log("Cache antiguo eliminado:", cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});