<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';

defineProps({
    coupons: Array,
});

const formatPrice = (value) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);

const valueLabel = (coupon) => (coupon.type === 'percent' ? `${coupon.value}%` : formatPrice(coupon.value));

const destroy = (coupon) => {
    if (confirm(`Hapus kupon "${coupon.code}"?`)) {
        router.delete(route('admin.coupons.destroy', coupon.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Kupon" />

    <AdminLayout>
        <template #title>Kupon &amp; Diskon</template>

        <div class="mb-4 flex justify-end">
            <Link
                :href="route('admin.coupons.create')"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
            >
                + Tambah Kupon
            </Link>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Nilai</th>
                        <th class="px-4 py-3">Min. Belanja</th>
                        <th class="px-4 py-3">Kuota</th>
                        <th class="px-4 py-3">Kedaluwarsa</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="coupon in coupons" :key="coupon.id">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ coupon.code }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ valueLabel(coupon) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ formatPrice(coupon.min_purchase) }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ coupon.quota ? `${coupon.used_count}/${coupon.quota}` : `${coupon.used_count}/∞` }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ coupon.expires_at ? new Date(coupon.expires_at).toLocaleDateString('id-ID') : '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="coupon.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                            >
                                {{ coupon.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="route('admin.coupons.edit', coupon.id)" class="mr-3 text-indigo-600 hover:underline">
                                Ubah
                            </Link>
                            <DangerButton @click="destroy(coupon)">Hapus</DangerButton>
                        </td>
                    </tr>
                    <tr v-if="coupons.length === 0">
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada kupon.</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>
    </AdminLayout>
</template>
