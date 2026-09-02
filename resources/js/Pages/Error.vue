<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    status: Number,
});

const page = usePage();

const messages = {
    403: 'Anda tidak memiliki akses ke halaman ini.',
    404: 'Halaman yang Anda cari tidak ditemukan.',
    419: 'Halaman ini sudah kedaluwarsa, silakan muat ulang dan coba lagi.',
    429: 'Terlalu banyak permintaan. Silakan coba lagi sebentar lagi.',
    500: 'Terjadi kesalahan pada server kami. Tim kami sudah diberi tahu.',
    503: 'Situs sedang dalam pemeliharaan singkat. Silakan coba lagi sebentar lagi.',
};

const message = computed(() => messages[props.status] ?? 'Terjadi kesalahan yang tidak terduga.');
</script>

<template>
    <Head :title="`Error ${status}`" />

    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-50 px-4 py-10 text-center">
        <p class="text-lg font-semibold text-gray-500">{{ page.props.storeSettings?.name ?? 'Toko Online' }}</p>
        <p class="mt-6 text-5xl font-bold text-indigo-600">{{ status }}</p>
        <p class="mt-4 max-w-sm text-gray-600">{{ message }}</p>
        <Link
            href="/"
            class="mt-8 inline-block rounded-md bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500"
        >
            Kembali ke Beranda
        </Link>
    </div>
</template>
