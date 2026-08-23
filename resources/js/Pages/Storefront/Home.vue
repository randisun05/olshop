<script setup>
import { Head } from '@inertiajs/vue3';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
</script>

<template>
    <Head title="Beranda" />

    <StorefrontLayout>
        <div class="rounded-lg bg-white p-10 text-center shadow">
            <h1 class="text-2xl font-bold text-gray-900">Selamat datang di Toko Online</h1>
            <p class="mt-2 text-gray-600">
                Katalog produk akan tampil di sini pada tahap pengembangan berikutnya.
            </p>
        </div>
    </StorefrontLayout>
</template>
