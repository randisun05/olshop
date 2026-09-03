<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    adjustments: Object,
    products: Array,
    filters: Object,
});

const form = useForm({
    product_id: '',
    product_variant_id: '',
    direction: 'in',
    quantity: 1,
    note: '',
});

const variantsForSelectedProduct = computed(() => {
    const product = props.products.find((p) => p.id === Number(form.product_id));

    return product ? product.variants : [];
});

const submit = () => {
    form.post(route('admin.stock-adjustments.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset('quantity', 'note'),
    });
};
</script>

<template>
    <Head title="Kartu Stok" />

    <AdminLayout>
        <template #title>Kartu Stok</template>

        <div class="mb-6 rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-sm font-semibold text-gray-800">Catat Penyesuaian Stok Manual</h2>
            <form class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5" @submit.prevent="submit">
                <div>
                    <InputLabel value="Produk" />
                    <select
                        v-model="form.product_id"
                        class="mt-1 block w-full rounded-md border-gray-300 text-sm"
                        @change="form.product_variant_id = ''"
                    >
                        <option value="" disabled>Pilih produk</option>
                        <option v-for="product in products" :key="product.id" :value="product.id">{{ product.name }}</option>
                    </select>
                    <InputError :message="form.errors.product_variant_id" />
                </div>
                <div>
                    <InputLabel value="Varian" />
                    <select v-model="form.product_variant_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                        <option value="" disabled>Pilih varian</option>
                        <option v-for="variant in variantsForSelectedProduct" :key="variant.id" :value="variant.id">
                            {{ variant.label }}
                        </option>
                    </select>
                </div>
                <div>
                    <InputLabel value="Jenis" />
                    <select v-model="form.direction" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                        <option value="in">Tambah stok</option>
                        <option value="out">Kurangi stok</option>
                    </select>
                </div>
                <div>
                    <InputLabel value="Jumlah" />
                    <TextInput v-model="form.quantity" type="number" min="1" class="mt-1 block w-full" />
                    <InputError :message="form.errors.quantity" />
                </div>
                <div>
                    <InputLabel value="Alasan" />
                    <TextInput v-model="form.note" type="text" class="mt-1 block w-full" placeholder="Mis. barang rusak" />
                    <InputError :message="form.errors.note" />
                </div>
                <div class="sm:col-span-2 lg:col-span-5">
                    <PrimaryButton :disabled="form.processing || !form.product_variant_id">Simpan Penyesuaian</PrimaryButton>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Produk</th>
                        <th class="px-4 py-3">Varian</th>
                        <th class="px-4 py-3">Jenis</th>
                        <th class="px-4 py-3 text-right">Perubahan</th>
                        <th class="px-4 py-3">Catatan</th>
                        <th class="px-4 py-3">Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="adjustment in adjustments.data" :key="adjustment.id">
                        <td class="px-4 py-3 text-gray-600">{{ adjustment.created_at }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ adjustment.product_name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ adjustment.variant_label }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ adjustment.type_label }}</td>
                        <td
                            class="px-4 py-3 text-right font-semibold"
                            :class="adjustment.quantity_change < 0 ? 'text-red-600' : 'text-green-600'"
                        >
                            {{ adjustment.quantity_change > 0 ? '+' : '' }}{{ adjustment.quantity_change }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ adjustment.note }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ adjustment.user }}</td>
                    </tr>
                    <tr v-if="adjustments.data.length === 0">
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada riwayat penyesuaian stok.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex flex-wrap gap-1">
            <Link
                v-for="link in adjustments.links"
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
