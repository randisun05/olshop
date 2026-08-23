<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object,
});

const search = ref(props.filters.q ?? '');

const formatPrice = (value) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);

const priceLabel = (product) =>
    product.min_price === product.max_price
        ? formatPrice(product.min_price)
        : `Mulai ${formatPrice(product.min_price)}`;

const applySearch = () => {
    router.get(route('catalog'), { ...props.filters, q: search.value }, { preserveState: true, replace: true });
};

const filterCategory = (slug) => {
    router.get(route('catalog'), { ...props.filters, category: slug || undefined }, { preserveState: true });
};

const applySort = (event) => {
    router.get(route('catalog'), { ...props.filters, sort: event.target.value }, { preserveState: true });
};
</script>

<template>
    <Head title="Katalog Produk" />

    <StorefrontLayout>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
            <aside class="space-y-4">
                <div>
                    <h3 class="mb-2 text-sm font-semibold text-gray-900">Kategori</h3>
                    <ul class="space-y-1 text-sm">
                        <li>
                            <button
                                class="text-left hover:text-indigo-600"
                                :class="{ 'font-semibold text-indigo-600': !filters.category }"
                                @click="filterCategory(null)"
                            >
                                Semua Produk
                            </button>
                        </li>
                        <li v-for="category in categories" :key="category.id">
                            <button
                                class="text-left hover:text-indigo-600"
                                :class="{ 'font-semibold text-indigo-600': filters.category === category.slug }"
                                @click="filterCategory(category.slug)"
                            >
                                {{ category.name }}
                            </button>
                            <ul v-if="category.children.length" class="ml-3 mt-1 space-y-1">
                                <li v-for="child in category.children" :key="child.id">
                                    <button
                                        class="text-left text-gray-600 hover:text-indigo-600"
                                        :class="{ 'font-semibold text-indigo-600': filters.category === child.slug }"
                                        @click="filterCategory(child.slug)"
                                    >
                                        {{ child.name }}
                                    </button>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </aside>

            <div class="md:col-span-3">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari produk..."
                        class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-64"
                        @keyup.enter="applySearch"
                    />
                    <select
                        class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :value="filters.sort ?? 'terbaru'"
                        @change="applySort"
                    >
                        <option value="terbaru">Terbaru</option>
                        <option value="nama">Nama A-Z</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <Link
                        v-for="product in products.data"
                        :key="product.id"
                        :href="route('catalog.show', product.slug)"
                        class="group rounded-lg bg-white p-3 shadow transition hover:shadow-md"
                    >
                        <div class="aspect-square overflow-hidden rounded bg-gray-100">
                            <img
                                v-if="product.image_url"
                                :src="product.image_url"
                                class="h-full w-full object-cover transition group-hover:scale-105"
                            />
                            <div v-else class="flex h-full items-center justify-center text-xs text-gray-400">
                                Tidak ada gambar
                            </div>
                        </div>
                        <h3 class="mt-2 line-clamp-2 text-sm font-medium text-gray-900">{{ product.name }}</h3>
                        <p class="mt-1 text-sm font-semibold text-indigo-600">{{ priceLabel(product) }}</p>
                        <p v-if="!product.in_stock" class="mt-1 text-xs text-red-500">Stok habis</p>
                    </Link>
                </div>

                <div v-if="products.data.length === 0" class="rounded-lg bg-white p-10 text-center text-gray-500 shadow">
                    Tidak ada produk ditemukan.
                </div>

                <div class="mt-6 flex flex-wrap gap-1">
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
            </div>
        </div>
    </StorefrontLayout>
</template>
