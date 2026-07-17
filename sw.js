/* Indique e Ganhe — service worker mínimo (instalação PWA). Sem offline completo. */
const CACHE_NAME = 'ig-static-v1';
const STATIC_PATH = /\/assets\//i;
const STATIC_EXT = /\.(?:css|js|png|jpe?g|webp|svg|gif|ico|woff2?|ttf|webmanifest)$/i;

function isApiPath(pathname) {
  return pathname === '/api' || pathname.startsWith('/api/');
}

function isAdminPath(pathname) {
  return pathname === '/admin' || pathname.startsWith('/admin/');
}

function isAuthenticatedAppPath(pathname) {
  const prefixes = [
    '/dashboard',
    '/perfil',
    '/meus-cupons',
    '/indicacoes',
    '/indicados',
    '/notificacoes',
    '/premios',
    '/configuracoes',
    '/logout',
  ];
  return prefixes.some((p) => pathname === p || pathname.startsWith(p + '/'));
}

function isStaticAsset(url) {
  return STATIC_PATH.test(url.pathname) || STATIC_EXT.test(url.pathname);
}

self.addEventListener('install', (event) => {
  event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const request = event.request;

  // Nunca interceptar POST ou outros métodos
  if (request.method !== 'GET') {
    return;
  }

  const url = new URL(request.url);
  if (url.origin !== self.location.origin) {
    return;
  }

  // Rede direta: API, Admin e áreas autenticadas (sem cache de dados pessoais)
  if (isApiPath(url.pathname) || isAdminPath(url.pathname) || isAuthenticatedAppPath(url.pathname)) {
    return;
  }

  // Assets estáticos públicos: cache-first
  if (isStaticAsset(url)) {
    event.respondWith(cacheFirst(request));
    return;
  }

  // Páginas GET públicas: network-first (sem persistir HTML autenticado)
  event.respondWith(networkFirst(request));
});

async function cacheFirst(request) {
  const cache = await caches.open(CACHE_NAME);
  const cached = await cache.match(request);
  if (cached) {
    return cached;
  }

  const response = await fetch(request);
  if (response && response.ok && response.type === 'basic') {
    cache.put(request, response.clone());
  }
  return response;
}

async function networkFirst(request) {
  try {
    return await fetch(request);
  } catch (err) {
    const cached = await caches.match(request);
    if (cached) {
      return cached;
    }
    throw err;
  }
}
