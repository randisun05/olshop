<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    from: String,
    to: String,
    products: Array,
});

const from = ref(props.from);
const to = ref(props.to);

const applyFilter = () => {
    router.get(route('admin.reports.top-products'), { from: from.value, to: to.value }, { preserveState: true });
};

const formatRupiah = (value) => 'Rp' + Number(value).toLocaleString('id-ID');
</script>

<template>
    <Head title="Produk Terlaris" />

    <AdminLayout>
        <template #title>Produk Terlaris</template>

        <form @submit.prevent="applyFilter" class="mb-6 flex flex-wrap items-end gap-4 rounded-lg bg-white p-4 shadow">
            <div>
                <InputLabel for="from" value="Dari Tanggal" />
                <TextInput id="from" v-model="from" type="date" />
            </div>
            <div>
                <InputLabel for="to" value="Sampai Tanggal" />
                <TextInput id="to" v-model="to" type="date" />
            </div>
            <PrimaryButton>Terapkan</PrimaryButton>
            <a
                :href="route('admin.reports.top-products.export.excel', { from, to })"
                class="ml-auto rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                Export Excel
            </a>
        </form>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Produk</th>
                        <th class="px-4 py-3 text-right">Jumlah Terjual</th>
                        <th class="px-4 py-3 text-right">Pendapatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="(product, index) in products" :key="product.product_name">
                        <td class="px-4 py-3 text-gray-500">{{ index + 1 }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ product.product_name }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">{{ product.qty_sold }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">{{ formatRupiah(product.revenue) }}</td>
                    </tr>
                    <tr v-if="products.length === 0">
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada penjualan pada periode ini.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
