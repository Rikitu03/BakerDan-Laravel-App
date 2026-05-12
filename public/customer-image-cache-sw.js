const CACHE_NAME = 'bakerdan-customer-image-cache-v1';
const CACHEABLE_IMAGE_PREFIXES = [
  '/images/bakerdan/',
  '/images/logo/',
];
const IMAGE_EXTENSION_PATTERN = /\.(?:avif|webp|png|jpe?g|gif)$/i;
const MAX_MESSAGE_IMAGES = 140;

const isCacheableImageRequest = (request) => {
  if (request.method !== 'GET') {
    return false;
  }

  const url = new URL(request.url);

  if (url.origin !== self.location.origin) {
    return false;
  }

  return CACHEABLE_IMAGE_PREFIXES.some((prefix) => url.pathname.startsWith(prefix))
    && IMAGE_EXTENSION_PATTERN.test(url.pathname);
};

const putResponseInCache = async (cache, request, response) => {
  if (!response || !response.ok) {
    return;
  }

  await cache.put(request, response.clone());
};

const fetchAndCache = async (cache, request) => {
  const response = await fetch(request);
  await putResponseInCache(cache, request, response);
  return response;
};

self.addEventListener('install', (event) => {
  event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const cacheNames = await caches.keys();
    await Promise.all(
      cacheNames
        .filter((cacheName) => cacheName.startsWith('bakerdan-customer-image-cache-') && cacheName !== CACHE_NAME)
        .map((cacheName) => caches.delete(cacheName)),
    );
    await self.clients.claim();
  })());
});

self.addEventListener('fetch', (event) => {
  if (!isCacheableImageRequest(event.request)) {
    return;
  }

  event.respondWith((async () => {
    const cache = await caches.open(CACHE_NAME);
    const cachedResponse = await cache.match(event.request);

    if (cachedResponse) {
      event.waitUntil(fetchAndCache(cache, event.request).catch(() => null));
      return cachedResponse;
    }

    try {
      return await fetchAndCache(cache, event.request);
    } catch (error) {
      return Response.error();
    }
  })());
});

self.addEventListener('message', (event) => {
  if (event.data?.type !== 'CACHE_IMAGES' || !Array.isArray(event.data.urls)) {
    return;
  }

  event.waitUntil((async () => {
    const cache = await caches.open(CACHE_NAME);
    const urls = event.data.urls.slice(0, MAX_MESSAGE_IMAGES);

    await Promise.allSettled(urls.map(async (rawUrl) => {
      const url = new URL(rawUrl, self.location.origin);
      const request = new Request(url.href, {
        credentials: 'same-origin',
        mode: 'same-origin',
      });

      if (!isCacheableImageRequest(request) || await cache.match(request)) {
        return;
      }

      await fetchAndCache(cache, request);
    }));
  })());
});
