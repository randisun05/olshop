<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

defineProps({
    orders: Object,
});

const formatPrice = (value) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
</script>

<template>
    <Head title="Pesanan Saya" />

    <CustomerLayout>
        <h1 class="mb-6 text-lg font-semibold text-gray-900">Pesanan Saya</h1>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">No. Pesanan</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Pembayaran</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="order in orders.data" :key="order.order_number">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ order.order_number }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ new Date(order.created_at).toLocaleDateString('id-ID') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ formatPrice(order.total) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ order.status_label }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ order.payment_status_label }}</td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="route('order.show', order.order_number)" class="text-indigo-600 hover:underline">
                                Detail
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="orders.data.length === 0">
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada pesanan.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </CustomerLayout>
</template>
