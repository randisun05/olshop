<script setup>
import { Head, Link } from '@inertiajs/vue3';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
</script>

<template>
    <Head title="Beranda" />

    <StorefrontLayout>
        <div class="rounded-lg bg-white p-10 text-center shadow">
            <h1 class="text-2xl font-bold text-gray-900">Selamat datang di Toko Online</h1>
            <p class="mt-2 text-gray-600">Temukan berbagai produk pilihan dengan harga terbaik.</p>
            <Link
                :href="route('catalog')"
                class="mt-6 inline-block rounded-md bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500"
            >
                Lihat Semua Produk
            </Link>
        </div>
    </StorefrontLayout>
</template>
