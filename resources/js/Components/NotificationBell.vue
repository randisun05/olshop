<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();
const open = ref(false);
const notifications = ref([]);
const loaded = ref(false);

const csrfToken = () => document.querySelector('meta[name="csrf-token"]').content;

const unreadCount = () => page.props.unreadNotificationCount ?? 0;

const fetchNotifications = async () => {
    const response = await fetch(route('admin.notifications.index'), {
        headers: { Accept: 'application/json' },
    });
    const data = await response.json();
    notifications.value = data.notifications;
    loaded.value = true;
};

const toggle = async () => {
    open.value = !open.value;
    if (open.value) {
        await fetchNotifications();
    }
};

const openNotification = async (notification) => {
    if (!notification.read) {
        await fetch(route('admin.notifications.mark-read', notification.id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken() },
        });
    }
    open.value = false;
    if (notification.url) {
        router.visit(notification.url);
    }
};

const markAllRead = async () => {
    await fetch(route('admin.notifications.mark-all-read'), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken() },
    });
    notifications.value = notifications.value.map((n) => ({ ...n, read: true }));
    router.reload({ only: ['unreadNotificationCount'] });
};

let interval = null;

onMounted(() => {
    interval = setInterval(() => router.reload({ only: ['unreadNotificationCount'] }), 20000);
});

onUnmounted(() => {
    if (interval) clearInterval(interval);
});
</script>

<template>
    <div class="relative">
        <button type="button" class="relative text-gray-500 hover:text-gray-700" @click="toggle">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                <path
                    fill-rule="evenodd"
                    d="M5.25 9a6.75 6.75 0 0 1 13.5 0v.75c0 2.123.8 4.057 2.118 5.52a.75.75 0 0 1-.297 1.206c-1.544.57-3.16.99-4.831 1.243a3.75 3.75 0 1 1-7.48 0 24.585 24.585 0 0 1-4.831-1.244.75.75 0 0 1-.298-1.205A8.217 8.217 0 0 0 5.25 9.75V9Zm4.502 8.9a2.25 2.25 0 1 0 4.496 0 25.057 25.057 0 0 1-4.496 0Z"
                    clip-rule="evenodd"
                />
            </svg>
            <span
                v-if="unreadCount() > 0"
                class="absolute -right-1 -top-1 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-semibold text-white"
            >
                {{ unreadCount() > 9 ? '9+' : unreadCount() }}
            </span>
        </button>

        <div
            v-if="open"
            class="absolute right-0 z-20 mt-2 w-80 rounded-md border bg-white shadow-lg"
        >
            <div class="flex items-center justify-between border-b px-4 py-2">
                <span class="text-sm font-semibold text-gray-800">Notifikasi</span>
                <button type="button" class="text-xs text-indigo-600 hover:underline" @click="markAllRead">
                    Tandai semua dibaca
                </button>
            </div>
            <div class="max-h-96 overflow-y-auto">
                <p v-if="loaded && notifications.length === 0" class="px-4 py-6 text-center text-sm text-gray-500">
                    Belum ada notifikasi.
                </p>
                <button
                    v-for="notification in notifications"
                    :key="notification.id"
                    type="button"
                    class="block w-full border-b px-4 py-3 text-left text-sm hover:bg-gray-50"
                    :class="{ 'bg-indigo-50': !notification.read }"
                    @click="openNotification(notification)"
                >
                    <p class="font-medium text-gray-800">{{ notification.title }}</p>
                    <p class="text-gray-600">{{ notification.body }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ notification.created_at }}</p>
                </button>
            </div>
        </div>
    </div>
</template>
