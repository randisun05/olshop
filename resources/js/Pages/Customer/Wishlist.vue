<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

defineProps({
    products: Array,
});

const formatPrice = (value) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);

const remove = (product) => {
    router.post(route('wishlist.toggle', product.id), {}, { preserveScroll: true });
};
</script>

<template>
    <Head title="Wishlist Saya" />

    <CustomerLayout>
        <h1 class="mb-6 text-lg font-semibold text-gray-900">Wishlist Saya</h1>

        <div v-if="products.length === 0" class="rounded-lg bg-white p-10 text-center text-gray-500 shadow">
            Belum ada produk di wishlist Anda.
            <Link :href="route('catalog')" class="mt-2 block text-indigo-600 hover:underline">Jelajahi produk</Link>
        </div>

        <div v-else class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div v-for="product in products" :key="product.id" class="rounded-lg bg-white p-3 shadow">
                <Link :href="route('catalog.show', product.slug)">
                    <div class="aspect-square overflow-hidden rounded bg-gray-100">
                        <img v-if="product.image_url" :src="product.image_url" class="h-full w-full object-cover" />
                    </div>
                    <h3 class="mt-2 line-clamp-2 text-sm font-medium text-gray-900">{{ product.name }}</h3>
                    <p class="mt-1 text-sm font-semibold text-indigo-600">{{ formatPrice(product.min_price) }}</p>
                    <p v-if="!product.in_stock" class="text-xs text-red-500">Stok habis</p>
                </Link>
                <button type="button" class="mt-2 text-xs text-red-600 hover:underline" @click="remove(product)">
                    Hapus dari Wishlist
                </button>
            </div>
        </div>
    </CustomerLayout>
</template>
