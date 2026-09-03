<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import NotificationBell from '@/Components/NotificationBell.vue';

const page = usePage();

const permissions = page.props.auth.user?.permissions ?? [];
const roles = page.props.auth.user?.roles ?? [];
const hasPermission = (name) => !name || permissions.includes(name);
const hasRole = (name) => !name || roles.includes(name);

const nav = [
    { label: 'Dashboard', route: 'admin.dashboard' },
    { label: 'Pesanan', route: 'admin.orders.index', permission: 'orders.manage' },
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
        <aside class="w-64 bg-gray-900 text-gray-200">
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

        <div class="flex-1">
            <header class="flex items-center justify-between border-b bg-white px-6 py-4">
                <h1 class="text-base font-semibold text-gray-800"><slot name="title">Admin</slot></h1>
                <div class="flex items-center gap-4 text-sm text-gray-600">
                    <NotificationBell />
                    <span>{{ page.props.auth.user?.name }}</span>
                    <Link :href="route('account.security.edit')" class="hover:text-indigo-600">Keamanan</Link>
                    <Link :href="route('logout')" method="post" as="button" class="text-red-600 hover:underline">
                        Keluar
                    </Link>
                </div>
            </header>

            <main class="p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
