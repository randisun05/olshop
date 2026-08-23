<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <header class="border-b bg-white">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4">
                <Link href="/" class="text-lg font-semibold text-gray-900">Toko Online</Link>

                <nav class="flex items-center gap-4 text-sm text-gray-700">
                    <Link :href="route('customer.dashboard')" class="hover:text-indigo-600">Akun Saya</Link>
                    <Link :href="route('customer.orders.index')" class="hover:text-indigo-600">Pesanan</Link>
                    <Link :href="route('customer.addresses.index')" class="hover:text-indigo-600">Alamat</Link>
                    <Link :href="route('cart.index')" class="hover:text-indigo-600">Keranjang</Link>
                    <span>{{ page.props.auth.user?.name }}</span>
                    <Link :href="route('logout')" method="post" as="button" class="hover:text-indigo-600">
                        Keluar
                    </Link>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-8">
            <slot />
        </main>
    </div>
</template>
