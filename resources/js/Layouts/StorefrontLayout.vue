<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
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

                <nav class="flex items-center gap-4 text-sm">
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
            </div>
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
    </div>
</template>
