<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    payments: Object,
    filters: Object,
});

const status = ref(props.filters.status ?? '');

const applyFilter = () => {
    router.get(route('admin.payments.index'), { status: status.value }, { preserveState: true });
};

const formatPrice = (value) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);

const verify = (payment) => {
    if (confirm(`Verifikasi pembayaran untuk pesanan ${payment.order_number}?`)) {
        router.post(route('admin.payments.verify', payment.id), {}, { preserveScroll: true });
    }
};

const reject = (payment) => {
    if (confirm(`Tolak pembayaran untuk pesanan ${payment.order_number}? Stok akan dikembalikan.`)) {
        router.post(route('admin.payments.reject', payment.id), {}, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Pembayaran" />

    <AdminLayout>
        <template #title>Verifikasi Pembayaran</template>

        <div class="mb-4">
            <select
                v-model="status"
                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                @change="applyFilter"
            >
                <option value="">Semua Status</option>
                <option value="pending">Menunggu</option>
                <option value="paid">Terbayar</option>
                <option value="failed">Gagal</option>
                <option value="expired">Kedaluwarsa</option>
            </select>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">No. Pesanan</th>
                        <th class="px-4 py-3">Metode</th>
                        <th class="px-4 py-3">Jumlah</th>
                        <th class="px-4 py-3">Bukti</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="payment in payments.data" :key="payment.id">
                        <td class="px-4 py-3">
                            <Link :href="route('order.show', payment.order_number)" class="text-indigo-600 hover:underline">
                                {{ payment.order_number }}
                            </Link>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ payment.method }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ formatPrice(payment.amount) }}</td>
                        <td class="px-4 py-3">
                            <a v-if="payment.proof_url" :href="payment.proof_url" target="_blank" class="text-indigo-600 hover:underline">
                                Lihat
                            </a>
                            <span v-else class="text-gray-400">-</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ payment.status_label }}</td>
                        <td class="px-4 py-3 text-right">
                            <template v-if="payment.status === 'pending'">
                                <button class="mr-3 text-green-600 hover:underline" @click="verify(payment)">Verifikasi</button>
                                <button class="text-red-600 hover:underline" @click="reject(payment)">Tolak</button>
                            </template>
                        </td>
                    </tr>
                    <tr v-if="payments.data.length === 0">
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">Tidak ada data pembayaran.</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>
    </AdminLayout>
</template>
