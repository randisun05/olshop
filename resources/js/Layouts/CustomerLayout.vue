<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const mobileMenuOpen = ref(false);

const navItems = [
    { label: 'Akun Saya', route: 'customer.dashboard' },
    { label: 'Pesanan', route: 'customer.orders.index' },
    { label: 'Retur/Komplain', route: 'customer.complaints.index' },
    { label: 'Chat CS', route: 'customer.chat.index', badge: 'unreadChatCount' },
    { label: 'Alamat', route: 'customer.addresses.index' },
    { label: 'Wishlist', route: 'wishlist.index' },
    { label: 'Keranjang', route: 'cart.index' },
    { label: 'Keamanan', route: 'account.security.edit' },
];
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <header class="border-b bg-white">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4">
                <Link href="/" class="text-lg font-semibold text-gray-900">Toko Online</Link>

                <nav class="hidden flex-wrap items-center gap-4 text-sm text-gray-700 md:flex">
                    <Link
                        v-for="item in navItems"
                        :key="item.route"
                        :href="route(item.route)"
                        class="relative hover:text-indigo-600"
                    >
                        {{ item.label }}
                        <span
                            v-if="item.badge && page.props[item.badge] > 0"
                            class="ml-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-xs font-semibold text-white"
                        >
                            {{ page.props[item.badge] }}
                        </span>
                    </Link>
                    <span>{{ page.props.auth.user?.name }}</span>
                    <Link :href="route('logout')" method="post" as="button" class="hover:text-indigo-600">
                        Keluar
                    </Link>
                </nav>

                <button
                    type="button"
                    class="text-gray-700 md:hidden"
                    aria-label="Buka menu"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                >
                    <svg v-if="!mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                        <path fill-rule="evenodd" d="M3 6.75A.75.75 0 0 1 3.75 6h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 6.75ZM3 12a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 12Zm0 5.25a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                        <path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 0 1 1.06 0L12 10.94l5.47-5.47a.75.75 0 1 1 1.06 1.06L13.06 12l5.47 5.47a.75.75 0 1 1-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 0 1-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <nav v-if="mobileMenuOpen" class="space-y-1 border-t px-4 py-3 text-sm text-gray-700 md:hidden">
                <Link
                    v-for="item in navItems"
                    :key="item.route"
                    :href="route(item.route)"
                    class="block py-1.5 hover:text-indigo-600"
                >
                    {{ item.label }}
                    <span
                        v-if="item.badge && page.props[item.badge] > 0"
                        class="ml-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-xs font-semibold text-white"
                    >
                        {{ page.props[item.badge] }}
                    </span>
                </Link>
                <p class="border-t py-1.5 text-gray-500">{{ page.props.auth.user?.name }}</p>
                <Link :href="route('logout')" method="post" as="button" class="block py-1.5 text-left hover:text-indigo-600">
                    Keluar
                </Link>
            </nav>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-8">
            <slot />
        </main>
    </div>
</template>
