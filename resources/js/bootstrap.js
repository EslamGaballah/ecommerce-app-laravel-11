import axios from 'axios';
import Pusher from 'pusher-js';

window.axios = axios;
window.Pusher = Pusher;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Enable logging (للتجربة فقط)
Pusher.logToConsole = true;

// إنشاء Pusher instance
const pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,

    authEndpoint: "/broadcasting/auth",

    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    }
});

// user id من Laravel
const userID = window.userID ?? null;

if (userID) {
    const channel = pusher.subscribe(`private-App.Models.User.${userID}`);

    channel.bind('Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', function (data) {
        console.log('Notification:', data);

        if (typeof window.addNotification === 'function') {
            window.addNotification(data);
        }
    });
}