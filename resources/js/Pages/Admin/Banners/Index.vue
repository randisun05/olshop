<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';

defineProps({
    banners: Array,
});

const destroy = (banner) => {
    if (confirm(`Hapus banner "${banner.title}"?`)) {
        router.delete(route('admin.banners.destroy', banner.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Banner" />

    <AdminLayout>
        <template #title>Banner Beranda</template>

        <div class="mb-4 flex justify-end">
            <Link
                :href="route('admin.banners.create')"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
            >
                + Tambah Banner
            </Link>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Gambar</th>
                        <th class="px-4 py-3">Judul</th>
                        <th class="px-4 py-3">Urutan</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="banner in banners" :key="banner.id">
                        <td class="px-4 py-3">
                            <img :src="`/storage/${banner.image}`" class="h-10 w-20 rounded object-cover" />
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ banner.title }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ banner.sort_order }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="banner.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                            >
                                {{ banner.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="route('admin.banners.edit', banner.id)" class="mr-3 text-indigo-600 hover:underline">
                                Ubah
                            </Link>
                            <DangerButton @click="destroy(banner)">Hapus</DangerButton>
                        </td>
                    </tr>
                    <tr v-if="banners.length === 0">
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada banner.</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>
    </AdminLayout>
</template>
