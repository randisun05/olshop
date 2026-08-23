<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
</script>

<template>
    <Head title="Dashboard Admin" />

    <AdminLayout>
        <template #title>Dashboard</template>

        <div class="rounded-lg bg-white p-6 shadow">
            <p class="text-gray-600">
                Ringkasan penjualan, produk, dan pesanan akan tampil di sini pada tahap
                pengembangan berikutnya.
            </p>
        </div>
    </AdminLayout>
</template>
