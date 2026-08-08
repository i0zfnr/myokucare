/* MyOKUcare Firebase messaging worker. Generated from non-secret public Firebase configuration. */
@if($enabled)
importScripts('https://www.gstatic.com/firebasejs/12.17.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/12.17.1/firebase-messaging-compat.js');

firebase.initializeApp(@json($firebaseConfig));
const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    const data = payload.data || {};
    self.registration.showNotification(data.title || 'MyOKUcare', {
        body: data.body || 'Terdapat perkembangan baharu pada akaun anda.',
        icon: '/icons/pwa-192.png',
        badge: '/icons/favicon-64.png',
        tag: data.tag || 'myokucare-update',
        renotify: false,
        data: { url: data.url || '/notifikasi' },
    });
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const target = new URL(event.notification.data?.url || '/notifikasi', self.location.origin);
    if (target.origin !== self.location.origin) return;
    event.waitUntil(clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windows) => {
        const existing = windows.find((client) => new URL(client.url).origin === target.origin);
        if (existing) {
            existing.navigate(target.href);
            return existing.focus();
        }
        return clients.openWindow(target.href);
    }));
});
@else
// Firebase push is disabled until production project credentials are configured.
@endif
