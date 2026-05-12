const LOCAL_HOSTS = new Set(['localhost', '127.0.0.1', '[::1]']);
const CACHEABLE_IMAGE_PREFIXES = ['/images/bakerdan/', '/images/logo/'];
const preloadedImages = new Set();

let serviceWorkerRegistrationPromise = null;

const canUseServiceWorker = () => {
  if (typeof window === 'undefined' || !('serviceWorker' in navigator)) {
    return false;
  }

  return window.isSecureContext || LOCAL_HOSTS.has(window.location.hostname);
};

const normalizeImageUrl = (source) => {
  if (!source || typeof window === 'undefined') {
    return null;
  }

  try {
    return new URL(source, window.location.origin).href;
  } catch (error) {
    return null;
  }
};

const uniqueImageUrls = (sources) => {
  return [...new Set((sources || []).map(normalizeImageUrl).filter(Boolean))];
};

const isSameOriginCacheableImage = (source) => {
  const url = normalizeImageUrl(source);

  if (!url) {
    return false;
  }

  const parsedUrl = new URL(url);

  return parsedUrl.origin === window.location.origin
    && CACHEABLE_IMAGE_PREFIXES.some((prefix) => parsedUrl.pathname.startsWith(prefix));
};

const runWhenIdle = (callback, timeout = 1200) => {
  if (typeof window === 'undefined') {
    return;
  }

  if ('requestIdleCallback' in window) {
    window.requestIdleCallback(callback, { timeout });
    return;
  }

  window.setTimeout(callback, 0);
};

const preloadOneImage = (url) => new Promise((resolve) => {
  const image = new window.Image();
  image.decoding = 'async';
  image.onload = () => resolve(url);
  image.onerror = () => {
    preloadedImages.delete(url);
    resolve(url);
  };
  image.src = url;
});

const preloadWithConcurrency = async (urls, concurrency) => {
  let cursor = 0;

  const workers = Array.from({ length: Math.min(concurrency, urls.length) }, async () => {
    while (cursor < urls.length) {
      const url = urls[cursor];
      cursor += 1;
      await preloadOneImage(url);
    }
  });

  await Promise.all(workers);
};

export const registerImageCacheServiceWorker = () => {
  if (!canUseServiceWorker()) {
    return Promise.resolve(null);
  }

  if (!serviceWorkerRegistrationPromise) {
    serviceWorkerRegistrationPromise = navigator.serviceWorker
      .register('/customer-image-cache-sw.js', { scope: '/' })
      .catch((error) => {
        console.warn('BakerDan image cache unavailable.', error);
        return null;
      });
  }

  return serviceWorkerRegistrationPromise;
};

export const preloadImages = (sources, options = {}) => {
  if (typeof window === 'undefined') {
    return Promise.resolve();
  }

  const {
    concurrency = 4,
    idle = false,
    limit = 24,
    timeout = 1200,
  } = options;

  const urls = uniqueImageUrls(sources)
    .filter((url) => !preloadedImages.has(url))
    .slice(0, limit);

  urls.forEach((url) => preloadedImages.add(url));

  if (!urls.length) {
    return Promise.resolve();
  }

  const run = () => preloadWithConcurrency(urls, concurrency);

  if (!idle) {
    return run();
  }

  return new Promise((resolve) => {
    runWhenIdle(() => {
      run().finally(resolve);
    }, timeout);
  });
};

export const cacheImages = async (sources, options = {}) => {
  if (typeof window === 'undefined') {
    return;
  }

  const { limit = 140 } = options;
  const urls = uniqueImageUrls(sources)
    .filter(isSameOriginCacheableImage)
    .slice(0, limit);

  if (!urls.length) {
    return;
  }

  if (!canUseServiceWorker()) {
    await preloadImages(urls, { concurrency: 3, idle: true, limit: Math.min(urls.length, 24) });
    return;
  }

  try {
    const registration = await registerImageCacheServiceWorker();
    const readyRegistration = registration?.active ? registration : await navigator.serviceWorker.ready;
    const worker = readyRegistration?.active || navigator.serviceWorker.controller;
    worker?.postMessage({ type: 'CACHE_IMAGES', urls });
  } catch (error) {
    await preloadImages(urls, { concurrency: 3, idle: true, limit: Math.min(urls.length, 24) });
  }
};
