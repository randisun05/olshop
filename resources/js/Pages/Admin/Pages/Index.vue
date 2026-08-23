<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';

defineProps({
    pages: Array,
});

const destroy = (page) => {
    if (confirm(`Hapus halaman "${page.title}"?`)) {
        router.delete(route('admin.pages.destroy', page.slug), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Halaman Statis" />

    <AdminLayout>
        <template #title>Halaman Statis</template>

        <div class="mb-4 flex justify-end">
            <Link
                :href="route('admin.pages.create')"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
            >
                + Tambah Halaman
            </Link>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Judul</th>
                        <th class="px-4 py-3">Slug</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="page in pages" :key="page.slug">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ page.title }}</td>
                        <td class="px-4 py-3 text-gray-600">/halaman/{{ page.slug }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="page.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                            >
                                {{ page.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="route('admin.pages.edit', page.slug)" class="mr-3 text-indigo-600 hover:underline">
                                Ubah
                            </Link>
                            <DangerButton @click="destroy(page)">Hapus</DangerButton>
                        </td>
                    </tr>
                    <tr v-if="pages.length === 0">
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada halaman.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
