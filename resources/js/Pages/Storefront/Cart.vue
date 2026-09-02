<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';

const props = defineProps({
    items: Array,
    subtotal: Number,
});

const formatPrice = (value) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);

const updateQuantity = (item, quantity) => {
    if (quantity < 1) return;
    router.put(route('cart.update', item.id), { quantity }, { preserveScroll: true });
};

const removeItem = (item) => {
    router.delete(route('cart.destroy', item.id), { preserveScroll: true });
};
</script>

<template>
    <Head title="Keranjang Belanja" />

    <StorefrontLayout>
        <h1 class="mb-6 text-xl font-semibold text-gray-900">Keranjang Belanja</h1>

        <div v-if="items.length === 0" class="rounded-lg bg-white p-10 text-center text-gray-500 shadow">
            Keranjang Anda masih kosong.
            <Link :href="route('catalog')" class="mt-2 block text-indigo-600 hover:underline">Mulai belanja</Link>
        </div>

        <div v-else class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-3 lg:col-span-2">
                <div v-for="item in items" :key="item.id" class="flex items-center gap-4 rounded-lg bg-white p-4 shadow">
                    <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded bg-gray-100">
                        <img
                            v-if="item.variant.product.image_url"
                            :src="item.variant.product.image_url"
                            :alt="item.variant.product.name"
                            class="h-full w-full object-cover"
                        />
                    </div>
                    <div class="flex-1">
                        <Link :href="route('catalog.show', item.variant.product.slug)" class="font-medium text-gray-900 hover:text-indigo-600">
                            {{ item.variant.product.name }}
                        </Link>
                        <p class="text-sm text-gray-500">{{ item.variant.label }}</p>
                        <p class="text-sm font-semibold text-indigo-600">{{ formatPrice(item.variant.price) }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input
                            type="number"
                            min="1"
                            :max="item.variant.stock"
                            :value="item.quantity"
                            class="w-16 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            @change="updateQuantity(item, Number($event.target.value))"
                        />
                        <button type="button" class="text-sm text-red-600 hover:underline" @click="removeItem(item)">
                            Hapus
                        </button>
                    </div>
                    <div class="w-28 text-right font-semibold text-gray-900">{{ formatPrice(item.subtotal) }}</div>
                </div>
            </div>

            <div class="h-fit rounded-lg bg-white p-6 shadow">
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Subtotal</span>
                    <span class="font-semibold text-gray-900">{{ formatPrice(subtotal) }}</span>
                </div>
                <p class="mt-1 text-xs text-gray-500">Ongkos kirim dihitung saat checkout.</p>
                <Link
                    :href="route('checkout.create')"
                    class="mt-4 block w-full rounded-md bg-indigo-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-indigo-500"
                >
                    Checkout
                </Link>
            </div>
        </div>
    </StorefrontLayout>
</template>
