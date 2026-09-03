<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object,
});

const search = ref(props.filters.search ?? '');
const categoryId = ref(props.filters.category_id ?? '');

const applyFilter = () => {
    router.get(
        route('admin.products.index'),
        { search: search.value, category_id: categoryId.value },
        { preserveState: true, replace: true }
    );
};

const formatPrice = (value) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);

const priceRange = (product) =>
    product.min_price === product.max_price
        ? formatPrice(product.min_price)
        : `${formatPrice(product.min_price)} - ${formatPrice(product.max_price)}`;

const destroy = (product) => {
    if (confirm(`Hapus produk "${product.name}"?`)) {
        router.delete(route('admin.products.destroy', product.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Produk" />

    <AdminLayout>
        <template #title>Produk</template>

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari produk..."
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    @keyup.enter="applyFilter"
                />
                <select
                    v-model="categoryId"
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    @change="applyFilter"
                >
                    <option value="">Semua Kategori</option>
                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <SecondaryButton @click="applyFilter">Cari</SecondaryButton>
            </div>

            <Link
                :href="route('admin.products.create')"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
            >
                + Tambah Produk
            </Link>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Brand</th>
                        <th class="px-4 py-3">Harga</th>
                        <th class="px-4 py-3">Stok</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="product in products.data" :key="product.id">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ product.name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ product.category ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ product.brand ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ priceRange(product) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ product.total_stock }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="product.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                            >
                                {{ product.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Link
                                :href="route('admin.products.edit', product.id)"
                                class="mr-3 text-indigo-600 hover:underline"
                            >
                                Ubah
                            </Link>
                            <DangerButton @click="destroy(product)">Hapus</DangerButton>
                        </td>
                    </tr>
                    <tr v-if="products.data.length === 0">
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada produk.</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-1">
            <Link
                v-for="link in products.links"
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
