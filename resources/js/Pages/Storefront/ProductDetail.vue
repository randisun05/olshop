<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';

const props = defineProps({
    product: Object,
    related: Array,
});

const activeImage = ref(props.product.images[0]?.url ?? null);
const selectedVariantId = ref(
    props.product.variants.find((v) => v.stock > 0)?.id ?? props.product.variants[0]?.id ?? null
);

const selectedVariant = computed(() =>
    props.product.variants.find((v) => v.id === selectedVariantId.value)
);

const formatPrice = (value) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
</script>

<template>
    <Head :title="product.name" />

    <StorefrontLayout>
        <nav class="mb-4 text-sm text-gray-500">
            <Link :href="route('catalog')" class="hover:text-indigo-600">Produk</Link>
            <span v-if="product.category"> / {{ product.category.name }}</span>
            <span> / {{ product.name }}</span>
        </nav>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
            <div>
                <div class="aspect-square overflow-hidden rounded-lg bg-gray-100">
                    <img v-if="activeImage" :src="activeImage" class="h-full w-full object-cover" />
                    <div v-else class="flex h-full items-center justify-center text-gray-400">Tidak ada gambar</div>
                </div>
                <div v-if="product.images.length > 1" class="mt-3 flex gap-2">
                    <button v-for="image in product.images" :key="image.id" @click="activeImage = image.url">
                        <img
                            :src="image.url"
                            class="h-16 w-16 rounded object-cover ring-2"
                            :class="activeImage === image.url ? 'ring-indigo-500' : 'ring-transparent'"
                        />
                    </button>
                </div>
            </div>

            <div>
                <p v-if="product.brand" class="text-sm text-gray-500">{{ product.brand.name }}</p>
                <h1 class="text-2xl font-bold text-gray-900">{{ product.name }}</h1>

                <p class="mt-3 text-2xl font-semibold text-indigo-600">
                    {{ selectedVariant ? formatPrice(selectedVariant.price) : '-' }}
                </p>

                <div v-if="product.variants.length > 1" class="mt-4">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Pilih Varian</label>
                    <select
                        v-model="selectedVariantId"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-72"
                    >
                        <option v-for="variant in product.variants" :key="variant.id" :value="variant.id">
                            {{ variant.label }} — {{ variant.stock > 0 ? `Stok ${variant.stock}` : 'Stok habis' }}
                        </option>
                    </select>
                </div>
                <p v-else-if="selectedVariant" class="mt-2 text-sm text-gray-600">
                    {{ selectedVariant.stock > 0 ? `Stok tersedia: ${selectedVariant.stock}` : 'Stok habis' }}
                </p>

                <button
                    type="button"
                    disabled
                    title="Keranjang belanja akan tersedia pada tahap pengembangan berikutnya"
                    class="mt-6 w-full cursor-not-allowed rounded-md bg-gray-300 px-4 py-2 text-sm font-semibold text-gray-600 sm:w-72"
                >
                    Tambah ke Keranjang (segera hadir)
                </button>

                <div v-if="product.description" class="mt-6 border-t pt-4 text-sm text-gray-700">
                    <h2 class="mb-2 font-semibold text-gray-900">Deskripsi</h2>
                    <p class="whitespace-pre-line">{{ product.description }}</p>
                </div>
            </div>
        </div>

        <div v-if="related.length" class="mt-12">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Produk Terkait</h2>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <Link
                    v-for="item in related"
                    :key="item.id"
                    :href="route('catalog.show', item.slug)"
                    class="rounded-lg bg-white p-3 shadow transition hover:shadow-md"
                >
                    <div class="aspect-square overflow-hidden rounded bg-gray-100">
                        <img v-if="item.image_url" :src="item.image_url" class="h-full w-full object-cover" />
                    </div>
                    <h3 class="mt-2 line-clamp-2 text-sm font-medium text-gray-900">{{ item.name }}</h3>
                    <p class="mt-1 text-sm font-semibold text-indigo-600">{{ formatPrice(item.min_price) }}</p>
                </Link>
            </div>
        </div>
    </StorefrontLayout>
</template>
