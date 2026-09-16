// Cache only this public, account-free app. Never cache authenticated Xinng pages.
const BASE=new URL('../../',self.location.href).pathname;
const CACHE='xinng-teleprompter-v3-'+BASE;
const SHELL=BASE+'tools/teleprompter/';
const FILES=[SHELL,BASE+'assets/css/style.css',BASE+'assets/css/brand.css',BASE+'assets/images/logo.svg',BASE+'assets/css/teleprompter.css',BASE+'assets/images/logo-icon.svg',...['app.mjs','core.mjs','imports.mjs'].map(name=>BASE+'assets/js/teleprompter/'+name)];
self.addEventListener('install',event=>{event.waitUntil(caches.open(CACHE).then(cache=>cache.addAll(FILES)).then(()=>self.skipWaiting()));});
self.addEventListener('activate',event=>{event.waitUntil(caches.keys().then(keys=>Promise.all(keys.filter(key=>key.startsWith('xinng-teleprompter-')&&key.endsWith('-'+BASE)&&key!==CACHE).map(key=>caches.delete(key)))).then(()=>self.clients.claim()));});
self.addEventListener('fetch',event=>{
  const url=new URL(event.request.url);
  if(event.request.method!=='GET'||url.origin!==self.location.origin)return;
  const path=url.pathname;
  if(!FILES.includes(path)&&path!==SHELL+'index.php')return;
  const key=path===SHELL+'index.php'?SHELL:path;
  event.respondWith(fetch(event.request).then(response=>{if(response.ok){const copy=response.clone();event.waitUntil(caches.open(CACHE).then(cache=>cache.put(key,copy)));}return response;}).catch(()=>caches.match(key).then(cached=>cached||Response.error())));
});
