<script setup>
import { Head } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
</script>

<template>
    <Head title="Akun Saya" />

    <CustomerLayout>
        <div class="rounded-lg bg-white p-6 shadow">
            <h1 class="mb-2 text-lg font-semibold text-gray-900">Akun Saya</h1>
            <p class="text-gray-600">
                Riwayat pesanan, alamat, dan wishlist akan tampil di sini pada tahap
                pengembangan berikutnya.
            </p>
        </div>
    </CustomerLayout>
</template>
