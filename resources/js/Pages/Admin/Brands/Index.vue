<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';

defineProps({
    brands: Array,
});

const destroy = (brand) => {
    if (confirm(`Hapus brand "${brand.name}"?`)) {
        router.delete(route('admin.brands.destroy', brand.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Brand" />

    <AdminLayout>
        <template #title>Brand</template>

        <div class="mb-4 flex justify-end">
            <Link
                :href="route('admin.brands.create')"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
            >
                + Tambah Brand
            </Link>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Logo</th>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Jumlah Produk</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="brand in brands" :key="brand.id">
                        <td class="px-4 py-3">
                            <img
                                v-if="brand.logo"
                                :src="`/storage/${brand.logo}`"
                                class="h-10 w-10 rounded object-cover"
                            />
                            <span v-else class="text-gray-400">-</span>
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ brand.name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ brand.products_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="route('admin.brands.edit', brand.id)" class="mr-3 text-indigo-600 hover:underline">
                                Ubah
                            </Link>
                            <DangerButton @click="destroy(brand)">Hapus</DangerButton>
                        </td>
                    </tr>
                    <tr v-if="brands.length === 0">
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada brand.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
