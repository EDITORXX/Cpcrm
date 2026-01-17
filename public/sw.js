// Service Worker for Real Estate CRM
const CACHE_NAME = 'real-estate-crm-v1';
const urlsToCache = [
  '/',
  '/login',
  '/favicon.ico',
  '/manifest.json'
];

// Install event - cache resources
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Service Worker: Cache opened');
        // Cache resources one by one to handle failures gracefully
        return Promise.allSettled(
          urlsToCache.map(url => {
            return fetch(url)
              .then(response => {
                if (response.ok) {
                  return cache.put(url, response);
                }
              })
              .catch(error => {
                console.warn(`Service Worker: Failed to cache ${url}:`, error);
              });
          })
        );
      })
      .then(() => {
        console.log('Service Worker: Installation complete');
      })
      .catch((error) => {
        console.error('Service Worker: Cache failed', error);
      })
  );
  self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('Service Worker: Deleting old cache', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  return self.clients.claim();
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
  // Only handle GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  // Skip cross-origin requests
  if (!event.request.url.startsWith(self.location.origin)) {
    return;
  }

  // Skip caching for HTML pages (always fetch fresh)
  if (event.request.destination === 'document' || 
      event.request.headers.get('accept').includes('text/html')) {
    event.respondWith(
      fetch(event.request).catch(() => {
        // Fallback to cache only if network fails
        return caches.match(event.request);
      })
    );
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        // Always fetch from network first for HTML pages
        if (event.request.destination === 'document') {
          return fetch(event.request)
            .then((networkResponse) => {
              return networkResponse;
            })
            .catch(() => {
              // Fallback to cache only if network fails
              return response;
            });
        }
        
        // Return cached version or fetch from network
        if (response) {
          return response;
        }
        return fetch(event.request)
          .then((networkResponse) => {
            // Cache successful responses (but not HTML pages)
            if (networkResponse && networkResponse.status === 200 && 
                event.request.destination !== 'document') {
              const responseToCache = networkResponse.clone();
              caches.open(CACHE_NAME).then((cache) => {
                cache.put(event.request, responseToCache);
              }).catch(() => {
                // Ignore cache errors
              });
            }
            return networkResponse;
          })
          .catch(() => {
            // If both fail, return offline page if available
            if (event.request.destination === 'document') {
              return caches.match('/');
            }
          });
      })
      .catch(() => {
        // Fallback to network
        return fetch(event.request);
      })
  );
});
