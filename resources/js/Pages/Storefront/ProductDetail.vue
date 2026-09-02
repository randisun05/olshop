<script setup>
import { computed, ref } from 'vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import Seo from '@/Components/Seo.vue';

const page = usePage();
const isLoggedIn = computed(() => !!page.props.auth.user);

const props = defineProps({
    product: Object,
    related: Array,
    seo: Object,
});

const activeImage = ref(props.product.images[0]?.url ?? null);
const selectedVariantId = ref(
    props.product.variants.find((v) => v.stock > 0)?.id ?? props.product.variants[0]?.id ?? null
);
const quantity = ref(1);

const selectedVariant = computed(() =>
    props.product.variants.find((v) => v.id === selectedVariantId.value)
);

// Schema.org Product markup — memungkinkan produk tampil sebagai rich result
// (harga, rating) di hasil pencarian Google. Berbeda dari meta description/
// Open Graph di Seo.vue (yang butuh render server-side agar terlihat crawler
// tanpa JS seperti WhatsApp), JSON-LD aman dirender via JS karena Google
// menjalankan JavaScript saat membaca structured data.
const jsonLd = computed(() => {
    const prices = props.product.variants.map((v) => Number(v.price));
    const inStock = props.product.variants.some((v) => v.stock > 0);

    const data = {
        '@context': 'https://schema.org/',
        '@type': 'Product',
        name: props.product.name,
        description: props.seo?.description,
        image: props.product.images.map((img) => img.url),
        brand: props.product.brand ? { '@type': 'Brand', name: props.product.brand.name } : undefined,
        offers: {
            '@type': 'AggregateOffer',
            priceCurrency: 'IDR',
            lowPrice: Math.min(...prices),
            highPrice: Math.max(...prices),
            availability: inStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        },
    };

    if (props.product.rating_avg) {
        data.aggregateRating = {
            '@type': 'AggregateRating',
            ratingValue: props.product.rating_avg,
            reviewCount: props.product.reviews_count,
        };
    }

    // Escape '<' agar teks produk yang memuat tag penutup script tidak bisa
    // memutus elemen ini lebih awal saat disisipkan lewat v-html.
    return JSON.stringify(data).replace(/</g, '\\u003c');
});

const formatPrice = (value) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);

const form = useForm({
    product_variant_id: null,
    quantity: 1,
});

const addToCart = () => {
    form.product_variant_id = selectedVariant.value.id;
    form.quantity = quantity.value;
    form.post(route('cart.store'), { preserveScroll: true });
};

const toggleWishlist = () => {
    if (!isLoggedIn.value) {
        router.visit(route('login'));

        return;
    }

    router.post(route('wishlist.toggle', props.product.id), {}, { preserveScroll: true });
};
</script>

<template>
    <Seo :seo="seo" />
    <component :is="'script'" type="application/ld+json" v-html="jsonLd" />

    <StorefrontLayout>
        <nav class="mb-4 text-sm text-gray-500">
            <Link :href="route('catalog')" class="hover:text-indigo-600">Produk</Link>
            <span v-if="product.category"> / {{ product.category.name }}</span>
            <span> / {{ product.name }}</span>
        </nav>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
            <div>
                <div class="aspect-square overflow-hidden rounded-lg bg-gray-100">
                    <img v-if="activeImage" :src="activeImage" :alt="product.name" class="h-full w-full object-cover" />
                    <div v-else class="flex h-full items-center justify-center text-gray-400">Tidak ada gambar</div>
                </div>
                <div v-if="product.images.length > 1" class="mt-3 flex gap-2">
                    <button v-for="(image, index) in product.images" :key="image.id" @click="activeImage = image.url">
                        <img
                            :src="image.url"
                            :alt="`${product.name} - gambar ${index + 1}`"
                            class="h-16 w-16 rounded object-cover ring-2"
                            :class="activeImage === image.url ? 'ring-indigo-500' : 'ring-transparent'"
                        />
                    </button>
                </div>
            </div>

            <div>
                <div class="flex items-start justify-between">
                    <div>
                        <p v-if="product.brand" class="text-sm text-gray-500">{{ product.brand.name }}</p>
                        <h1 class="text-2xl font-bold text-gray-900">{{ product.name }}</h1>
                    </div>
                    <button
                        type="button"
                        class="rounded-full border p-2 text-lg"
                        :class="product.is_wishlisted ? 'border-red-300 text-red-500' : 'border-gray-300 text-gray-400 hover:text-red-500'"
                        title="Tambah ke wishlist"
                        @click="toggleWishlist"
                    >
                        ♥
                    </button>
                </div>

                <p v-if="product.reviews_count" class="mt-1 text-sm text-gray-600">
                    ★ {{ product.rating_avg }} ({{ product.reviews_count }} ulasan)
                </p>

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

                <div class="mt-6 flex items-center gap-3 sm:w-72">
                    <input
                        v-model.number="quantity"
                        type="number"
                        min="1"
                        :max="selectedVariant?.stock ?? 1"
                        class="w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <button
                        type="button"
                        :disabled="!selectedVariant || selectedVariant.stock < 1 || form.processing"
                        class="flex-1 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:bg-gray-300"
                        @click="addToCart"
                    >
                        {{ selectedVariant?.stock > 0 ? 'Tambah ke Keranjang' : 'Stok Habis' }}
                    </button>
                </div>

                <div v-if="product.description" class="mt-6 border-t pt-4 text-sm text-gray-700">
                    <h2 class="mb-2 font-semibold text-gray-900">Deskripsi</h2>
                    <p class="whitespace-pre-line">{{ product.description }}</p>
                </div>
            </div>
        </div>

        <div class="mt-12">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">
                Ulasan Pembeli <span v-if="product.reviews_count">({{ product.reviews_count }})</span>
            </h2>
            <div v-if="product.reviews.length === 0" class="rounded-lg bg-white p-6 text-sm text-gray-500 shadow">
                Belum ada ulasan untuk produk ini.
            </div>
            <div v-else class="space-y-3">
                <div v-for="(review, i) in product.reviews" :key="i" class="rounded-lg bg-white p-4 shadow">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-gray-900">{{ review.user_name }}</span>
                        <span class="text-sm text-amber-500">★ {{ review.rating }}</span>
                    </div>
                    <p class="mt-1 text-xs text-gray-400">{{ new Date(review.created_at).toLocaleDateString('id-ID') }}</p>
                    <p v-if="review.comment" class="mt-2 text-sm text-gray-700">{{ review.comment }}</p>
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
                        <img v-if="item.image_url" :src="item.image_url" :alt="item.name" class="h-full w-full object-cover" />
                    </div>
                    <h3 class="mt-2 line-clamp-2 text-sm font-medium text-gray-900">{{ item.name }}</h3>
                    <p class="mt-1 text-sm font-semibold text-indigo-600">{{ formatPrice(item.min_price) }}</p>
                </Link>
            </div>
        </div>
    </StorefrontLayout>
</template>
