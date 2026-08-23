<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    logs: Object,
});
</script>

<template>
    <Head title="Log Aktivitas" />

    <AdminLayout>
        <template #title>Log Aktivitas Admin</template>

        <p class="mb-4 text-sm text-gray-500">
            Riwayat aksi kritikal (hapus produk/kupon/banner/halaman, verifikasi/tolak pembayaran,
            batalkan pesanan, ubah pengaturan toko). Hanya bisa dilihat oleh Super Admin.
        </p>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Pengguna</th>
                        <th class="px-4 py-3">Aksi</th>
                        <th class="px-4 py-3">Deskripsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="log in logs.data" :key="log.id">
                        <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ log.created_at }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ log.user }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ log.action }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ log.description }}</td>
                    </tr>
                    <tr v-if="logs.data.length === 0">
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada aktivitas tercatat.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex flex-wrap gap-1">
            <Link
                v-for="link in logs.links"
                :key="link.label"
                :href="link.url ?? '#'"
                v-html="link.label"
                class="rounded px-3 py-1 text-sm"
                :class="[
                    link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50',
                    !link.url && 'pointer-events-none opacity-50',
                ]"
            />
        </div>
    </AdminLayout>
</template>
