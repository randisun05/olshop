<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    orders: Object,
    filters: Object,
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

const applyFilter = () => {
    router.get(route('admin.orders.index'), { search: search.value, status: status.value }, { preserveState: true });
};

const formatPrice = (value) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);

const statusBadgeClass = (status) => ({
    pending_payment: 'bg-yellow-100 text-yellow-700',
    paid: 'bg-blue-100 text-blue-700',
    processing: 'bg-indigo-100 text-indigo-700',
    shipped: 'bg-purple-100 text-purple-700',
    completed: 'bg-green-100 text-green-700',
    cancelled: 'bg-gray-100 text-gray-600',
}[status] ?? 'bg-gray-100 text-gray-600');
</script>

<template>
    <Head title="Pesanan" />

    <AdminLayout>
        <template #title>Pesanan</template>

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <input
                v-model="search"
                type="text"
                placeholder="Cari nomor pesanan / nama / email..."
                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                @keyup.enter="applyFilter"
            />
            <select
                v-model="status"
                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                @change="applyFilter"
            >
                <option value="">Semua Status</option>
                <option value="pending_payment">Menunggu Pembayaran</option>
                <option value="paid">Sudah Dibayar</option>
                <option value="processing">Diproses</option>
                <option value="shipped">Dikirim</option>
                <option value="completed">Selesai</option>
                <option value="cancelled">Dibatalkan</option>
            </select>
            <button
                type="button"
                class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                @click="applyFilter"
            >
                Cari
            </button>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">No. Pesanan</th>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Pembayaran</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="order in orders.data" :key="order.order_number">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ order.order_number }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ order.customer }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ formatPrice(order.total) }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="statusBadgeClass(order.status)">
                                {{ order.status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ order.payment_status_label ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="route('admin.orders.show', order.order_number)" class="text-indigo-600 hover:underline">
                                Detail
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="orders.data.length === 0">
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">Tidak ada pesanan.</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-1">
            <Link
                v-for="link in orders.links"
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
