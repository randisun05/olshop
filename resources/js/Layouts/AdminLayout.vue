<script setup>
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import NotificationBell from '@/Components/NotificationBell.vue';

const page = usePage();
const sidebarOpen = ref(false);

router.on('navigate', () => {
    sidebarOpen.value = false;
});

const permissions = page.props.auth.user?.permissions ?? [];
const roles = page.props.auth.user?.roles ?? [];
const hasPermission = (name) => {
    if (!name) return true;
    const names = Array.isArray(name) ? name : [name];
    return names.some((n) => permissions.includes(n));
};
const hasRole = (name) => !name || roles.includes(name);

const nav = [
    { label: 'Dashboard', route: 'admin.dashboard' },
    { label: 'Pesanan', route: 'admin.orders.index', permission: ['orders.manage', 'orders.fulfill'] },
    { label: 'Produk', route: 'admin.products.index', permission: 'products.manage' },
    { label: 'Kategori', route: 'admin.categories.index', permission: 'categories.manage' },
    { label: 'Brand', route: 'admin.brands.index', permission: 'brands.manage' },
    { label: 'Atribut', route: 'admin.attributes.index', permission: 'attributes.manage' },
    { label: 'Wilayah Kirim', route: 'admin.shipping-zones.index', permission: 'shipping.manage' },
    { label: 'Pembayaran', route: 'admin.payments.index', permission: 'orders.manage' },
    { label: 'Kupon', route: 'admin.coupons.index', permission: 'coupons.manage' },
    { label: 'Ulasan', route: 'admin.reviews.index', permission: 'reviews.manage' },
    { label: 'Retur/Komplain', route: 'admin.complaints.index', permission: 'complaints.manage' },
    { label: 'Chat', route: 'admin.chat.index', permission: 'chat.manage', badge: page.props.unreadChatCount },
    { label: 'FAQ Chatbot', route: 'admin.faq.index', permission: ['chat.manage', 'pages.manage'] },
    { label: 'Laporan Penjualan', route: 'admin.reports.sales', permission: 'reports.view' },
    { label: 'Produk Terlaris', route: 'admin.reports.top-products', permission: 'reports.view' },
    { label: 'Laporan Stok', route: 'admin.reports.stock', permission: 'reports.view' },
    { label: 'Kartu Stok', route: 'admin.stock-adjustments.index', permission: 'products.manage' },
    { label: 'Banner', route: 'admin.banners.index', permission: 'banners.manage' },
    { label: 'Halaman', route: 'admin.pages.index', permission: 'pages.manage' },
    { label: 'Impor Data', route: 'admin.imports.index', permission: 'products.manage' },
    { label: 'Pengaturan', route: 'admin.settings.edit', permission: 'settings.manage' },
    { label: 'Pengguna Staf', route: 'admin.users.index', role: 'Super Admin' },
    { label: 'Log Aktivitas', route: 'admin.activity-logs.index', role: 'Super Admin' },
].filter((item) => hasPermission(item.permission) && hasRole(item.role));
</script>

<template>
    <div class="flex min-h-screen bg-gray-100">
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-30 bg-black/50 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <aside
            class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full overflow-y-auto bg-gray-900 text-gray-200 transition-transform duration-200 lg:static lg:translate-x-0"
            :class="{ 'translate-x-0': sidebarOpen }"
        >
            <div class="px-6 py-5 text-lg font-semibold text-white">Admin</div>
            <nav class="mt-2 space-y-1 px-3">
                <Link
                    v-for="item in nav"
                    :key="item.route"
                    :href="route(item.route)"
                    class="flex items-center justify-between rounded px-3 py-2 text-sm hover:bg-gray-800"
                    :class="{ 'bg-gray-800 text-white': route().current(item.route.replace('.index', '').concat('.*')) || route().current(item.route) }"
                >
                    {{ item.label }}
                    <span v-if="item.badge" class="rounded-full bg-red-600 px-2 py-0.5 text-xs font-semibold text-white">
                        {{ item.badge }}
                    </span>
                </Link>
            </nav>
        </aside>

        <div class="min-w-0 flex-1">
            <header class="flex items-center justify-between gap-3 border-b bg-white px-4 py-4 sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <button
                        type="button"
                        class="text-gray-600 lg:hidden"
                        aria-label="Buka menu"
                        @click="sidebarOpen = !sidebarOpen"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                            <path fill-rule="evenodd" d="M3 6.75A.75.75 0 0 1 3.75 6h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 6.75ZM3 12a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 12Zm0 5.25a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <h1 class="truncate text-base font-semibold text-gray-800"><slot name="title">Admin</slot></h1>
                </div>
                <div class="flex shrink-0 items-center gap-3 text-sm text-gray-600 sm:gap-4">
                    <NotificationBell />
                    <span class="hidden sm:inline">{{ page.props.auth.user?.name }}</span>
                    <Link :href="route('account.security.edit')" class="hidden hover:text-indigo-600 sm:inline">Keamanan</Link>
                    <Link :href="route('logout')" method="post" as="button" class="text-red-600 hover:underline">
                        Keluar
                    </Link>
                </div>
            </header>

            <main class="overflow-x-hidden p-4 sm:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
