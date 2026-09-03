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
    summary: Object,
    orders: Array,
});

const from = ref(props.from);
const to = ref(props.to);

const applyFilter = () => {
    router.get(route('admin.reports.sales'), { from: from.value, to: to.value }, { preserveState: true });
};

const formatRupiah = (value) => 'Rp' + Number(value).toLocaleString('id-ID');
</script>

<template>
    <Head title="Laporan Penjualan" />

    <AdminLayout>
        <template #title>Laporan Penjualan</template>

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
            <div class="ml-auto flex gap-3 text-sm">
                <a
                    :href="route('admin.reports.sales.export.excel', { from, to })"
                    class="rounded-md border border-gray-300 px-3 py-2 font-medium text-gray-700 hover:bg-gray-50"
                >
                    Export Excel
                </a>
                <a
                    :href="route('admin.reports.sales.export.pdf', { from, to })"
                    class="rounded-md border border-gray-300 px-3 py-2 font-medium text-gray-700 hover:bg-gray-50"
                >
                    Export PDF
                </a>
            </div>
        </form>

        <div class="mb-6 grid grid-cols-2 gap-4">
            <div class="rounded-lg bg-white p-5 shadow">
                <p class="text-sm text-gray-500">Jumlah Pesanan</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ summary.total_orders }}</p>
            </div>
            <div class="rounded-lg bg-white p-5 shadow">
                <p class="text-sm text-gray-500">Total Omzet</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ formatRupiah(summary.total_revenue) }}</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Nomor Pesanan</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="order in orders" :key="order.order_number">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ order.order_number }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ order.date }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ order.customer }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ order.status }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">{{ formatRupiah(order.total) }}</td>
                    </tr>
                    <tr v-if="orders.length === 0">
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">Tidak ada penjualan pada periode ini.</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>
    </AdminLayout>
</template>
