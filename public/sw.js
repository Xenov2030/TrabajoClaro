/* TrabajoClaro PWA v1. Network only; no CacheStorage, precache, offline fallback,
   private navigation interception or background synchronization. */
const PUBLIC_ASSET = /^\/(?:_next\/static\/|brand\/|fonts\/|favicon(?:[.-]|$)|icon-(?:192|512)(?:-maskable)?\.png$|apple-touch-icon\.png$|site\.webmanifest$)/;
self.addEventListener('install', () => {
 // Do not skipWaiting: an update must not replace/reload an editing session.
});
self.addEventListener('fetch', event => {
 const request=event.request,url=new URL(request.url);
 if(request.method!=='GET'||url.origin!==self.location.origin||request.mode==='navigate'||!PUBLIC_ASSET.test(url.pathname))return;
 event.respondWith(fetch(request)); // Public resources only; browser HTTP cache rules apply.
});
// No clients.claim(), automatic reload, push, sync, or API/document caching.
