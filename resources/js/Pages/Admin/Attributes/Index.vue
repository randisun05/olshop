<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';

defineProps({
    attributes: Array,
});

const destroy = (attribute) => {
    if (confirm(`Hapus atribut "${attribute.name}"? Nilai yang masih dipakai produk akan tetap dipertahankan.`)) {
        router.delete(route('admin.attributes.destroy', attribute.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Atribut" />

    <AdminLayout>
        <template #title>Atribut Produk</template>

        <p class="mb-4 text-sm text-gray-600">
            Atribut (mis. Warna, Ukuran) dipakai untuk membuat varian produk seperti "Merah / L".
        </p>

        <div class="mb-4 flex justify-end">
            <Link
                :href="route('admin.attributes.create')"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
            >
                + Tambah Atribut
            </Link>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Nilai</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="attribute in attributes" :key="attribute.id">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ attribute.name }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ attribute.values.map((v) => v.value).join(', ') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Link
                                :href="route('admin.attributes.edit', attribute.id)"
                                class="mr-3 text-indigo-600 hover:underline"
                            >
                                Ubah
                            </Link>
                            <DangerButton @click="destroy(attribute)">Hapus</DangerButton>
                        </td>
                    </tr>
                    <tr v-if="attributes.length === 0">
                        <td colspan="3" class="px-4 py-6 text-center text-gray-500">Belum ada atribut.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
