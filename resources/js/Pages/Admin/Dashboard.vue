<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    summary: Object,
    chart: Array,
    topProducts: Array,
    lowStock: Array,
});

const canvas = ref(null);
let chartInstance = null;

const formatRupiah = (value) => 'Rp' + Number(value).toLocaleString('id-ID');

onMounted(() => {
    chartInstance = new Chart(canvas.value, {
        type: 'line',
        data: {
            labels: props.chart.map((d) => d.date.slice(5)),
            datasets: [
                {
                    label: 'Penjualan (Rp)',
                    data: props.chart.map((d) => d.total),
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    tension: 0.3,
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } },
        },
    });
});

onUnmounted(() => {
    chartInstance?.destroy();
});
</script>

<template>
    <Head title="Dashboard Admin" />

    <AdminLayout>
        <template #title>Dashboard</template>

        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg bg-white p-5 shadow">
                <p class="text-sm text-gray-500">Omzet Bulan Ini</p>
                <p class="mt-1 text-xl font-semibold text-gray-900">{{ formatRupiah(summary.revenue_this_month) }}</p>
            </div>
            <div class="rounded-lg bg-white p-5 shadow">
                <p class="text-sm text-gray-500">Pesanan Bulan Ini</p>
                <p class="mt-1 text-xl font-semibold text-gray-900">{{ summary.orders_this_month }}</p>
            </div>
            <div class="rounded-lg bg-white p-5 shadow">
                <p class="text-sm text-gray-500">Menunggu Pembayaran</p>
                <p class="mt-1 text-xl font-semibold text-gray-900">{{ summary.pending_payment }}</p>
            </div>
            <div class="rounded-lg bg-white p-5 shadow">
                <p class="text-sm text-gray-500">Varian Stok Menipis</p>
                <p class="mt-1 text-xl font-semibold text-red-600">{{ summary.low_stock_count }}</p>
            </div>
        </div>

        <div class="mb-6 rounded-lg bg-white p-5 shadow">
            <h2 class="mb-3 text-sm font-semibold text-gray-700">Tren Penjualan 30 Hari Terakhir</h2>
            <canvas ref="canvas" height="90"></canvas>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="rounded-lg bg-white p-5 shadow">
                <h2 class="mb-3 text-sm font-semibold text-gray-700">Produk Terlaris</h2>
                <ul class="divide-y divide-gray-100 text-sm">
                    <li v-for="product in topProducts" :key="product.product_name" class="flex justify-between py-2">
                        <span class="text-gray-800">{{ product.product_name }}</span>
                        <span class="font-medium text-gray-900">{{ product.qty_sold }} terjual</span>
                    </li>
                    <li v-if="topProducts.length === 0" class="py-2 text-gray-500">Belum ada penjualan.</li>
                </ul>
            </div>
            <div class="rounded-lg bg-white p-5 shadow">
                <h2 class="mb-3 text-sm font-semibold text-gray-700">Stok Menipis</h2>
                <ul class="divide-y divide-gray-100 text-sm">
                    <li v-for="variant in lowStock" :key="variant.product_name + variant.variant_label" class="flex justify-between py-2">
                        <span class="text-gray-800">{{ variant.product_name }} ({{ variant.variant_label }})</span>
                        <span class="font-medium text-red-600">{{ variant.stock }} unit</span>
                    </li>
                    <li v-if="lowStock.length === 0" class="py-2 text-gray-500">Tidak ada stok menipis.</li>
                </ul>
            </div>
        </div>
    </AdminLayout>
</template>
