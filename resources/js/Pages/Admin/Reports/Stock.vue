<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    threshold: Number,
    variants: Array,
});

const formatRupiah = (value) => 'Rp' + Number(value).toLocaleString('id-ID');
</script>

<template>
    <Head title="Laporan Stok" />

    <AdminLayout>
        <template #title>Laporan Stok</template>

        <div class="mb-4 flex items-center justify-between">
            <p class="text-sm text-gray-500">Varian dengan stok &le; {{ threshold }} ditandai sebagai stok menipis.</p>
            <a
                :href="route('admin.reports.stock.export.excel')"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                Export Excel
            </a>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Produk</th>
                        <th class="px-4 py-3">Varian</th>
                        <th class="px-4 py-3">SKU</th>
                        <th class="px-4 py-3 text-right">Harga</th>
                        <th class="px-4 py-3 text-right">Stok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="variant in variants" :key="variant.id" :class="{ 'bg-red-50': variant.is_low }">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ variant.product_name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ variant.variant_label }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ variant.sku }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">{{ formatRupiah(variant.price) }}</td>
                        <td class="px-4 py-3 text-right font-semibold" :class="variant.is_low ? 'text-red-600' : 'text-gray-900'">
                            {{ variant.stock }}
                        </td>
                    </tr>
                    <tr v-if="variants.length === 0">
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada varian produk.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
