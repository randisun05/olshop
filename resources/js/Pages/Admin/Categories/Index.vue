<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';

defineProps({
    categories: Array,
});

const destroy = (category) => {
    if (confirm(`Hapus kategori "${category.name}"?`)) {
        router.delete(route('admin.categories.destroy', category.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Kategori" />

    <AdminLayout>
        <template #title>Kategori</template>

        <div class="mb-4 flex justify-end">
            <Link
                :href="route('admin.categories.create')"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
            >
                + Tambah Kategori
            </Link>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Induk</th>
                        <th class="px-4 py-3">Jumlah Produk</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="category in categories" :key="category.id">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ category.name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ category.parent?.name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ category.products_count }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="category.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                            >
                                {{ category.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Link
                                :href="route('admin.categories.edit', category.id)"
                                class="mr-3 text-indigo-600 hover:underline"
                            >
                                Ubah
                            </Link>
                            <DangerButton @click="destroy(category)">Hapus</DangerButton>
                        </td>
                    </tr>
                    <tr v-if="categories.length === 0">
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada kategori.</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>
    </AdminLayout>
</template>
