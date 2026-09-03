<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import CookieConsentBanner from '@/Components/CookieConsentBanner.vue';

const page = usePage();
const mobileMenuOpen = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <header class="border-b bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
                <Link href="/" class="flex items-center gap-2 text-lg font-semibold text-gray-900">
                    <img
                        v-if="page.props.storeSettings.logoUrl"
                        :src="page.props.storeSettings.logoUrl"
                        :alt="page.props.storeSettings.name"
                        class="h-8 w-8 rounded object-cover"
                    />
                    {{ page.props.storeSettings.name }}
                </Link>

                <nav class="hidden items-center gap-4 text-sm sm:flex">
                    <Link :href="route('catalog')" class="text-gray-700 hover:text-indigo-600">Produk</Link>
                    <Link :href="route('order.lookup')" class="text-gray-700 hover:text-indigo-600">Lacak Pesanan</Link>
                    <Link :href="route('cart.index')" class="relative text-gray-700 hover:text-indigo-600">
                        Keranjang
                        <span
                            v-if="page.props.cart.count > 0"
                            class="ml-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-indigo-600 px-1 text-xs font-semibold text-white"
                        >
                            {{ page.props.cart.count }}
                        </span>
                    </Link>
                    <template v-if="page.props.auth.user">
                        <Link :href="route('dashboard')" class="text-gray-700 hover:text-indigo-600">
                            Dashboard
                        </Link>
                        <Link :href="route('logout')" method="post" as="button" class="text-gray-700 hover:text-indigo-600">
                            Keluar
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" class="text-gray-700 hover:text-indigo-600">Masuk</Link>
                        <Link :href="route('register')" class="text-gray-700 hover:text-indigo-600">Daftar</Link>
                    </template>
                </nav>

                <div class="flex items-center gap-3 sm:hidden">
                    <Link :href="route('cart.index')" class="relative text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                            <path d="M2.25 2.25a.75.75 0 0 0 0 1.5h1.386c.17 0 .318.114.362.278l2.558 9.592a3.752 3.752 0 0 0-2.806 3.63c0 .414.336.75.75.75h15.75a.75.75 0 0 0 0-1.5H5.378A2.25 2.25 0 0 1 7.5 15h11.218a.75.75 0 0 0 .674-.421l3.75-7.5a.75.75 0 0 0-.671-1.079H5.397l-.415-1.556A1.875 1.875 0 0 0 3.187 2.25H2.25Z" />
                            <path d="M8.25 21a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3ZM18.75 21a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z" />
                        </svg>
                        <span
                            v-if="page.props.cart.count > 0"
                            class="absolute -right-2 -top-2 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-indigo-600 px-1 text-[10px] font-semibold text-white"
                        >
                            {{ page.props.cart.count }}
                        </span>
                    </Link>
                    <button
                        type="button"
                        class="text-gray-700"
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
            </div>

            <nav v-if="mobileMenuOpen" class="space-y-1 border-t px-4 py-3 text-sm sm:hidden">
                <Link :href="route('catalog')" class="block py-1.5 text-gray-700 hover:text-indigo-600">Produk</Link>
                <Link :href="route('order.lookup')" class="block py-1.5 text-gray-700 hover:text-indigo-600">Lacak Pesanan</Link>
                <template v-if="page.props.auth.user">
                    <Link :href="route('dashboard')" class="block py-1.5 text-gray-700 hover:text-indigo-600">Dashboard</Link>
                    <Link :href="route('logout')" method="post" as="button" class="block py-1.5 text-left text-gray-700 hover:text-indigo-600">
                        Keluar
                    </Link>
                </template>
                <template v-else>
                    <Link :href="route('login')" class="block py-1.5 text-gray-700 hover:text-indigo-600">Masuk</Link>
                    <Link :href="route('register')" class="block py-1.5 text-gray-700 hover:text-indigo-600">Daftar</Link>
                </template>
            </nav>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8">
            <slot />
        </main>

        <footer class="mt-8 border-t bg-white">
            <div class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-6 text-sm text-gray-500 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ new Date().getFullYear() }} {{ page.props.storeSettings.name }}</p>
                <nav v-if="page.props.footerPages.length" class="flex flex-wrap gap-4">
                    <Link
                        v-for="p in page.props.footerPages"
                        :key="p.slug"
                        :href="route('page.show', p.slug)"
                        class="hover:text-indigo-600"
                    >
                        {{ p.title }}
                    </Link>
                </nav>
            </div>
        </footer>

        <CookieConsentBanner v-if="page.props.analyticsEnabled" />
    </div>
</template>
