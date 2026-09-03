<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

defineProps({
    complaints: Object,
});
</script>

<template>
    <Head title="Retur & Komplain" />

    <CustomerLayout>
        <h1 class="mb-6 text-lg font-semibold text-gray-900">Retur & Komplain Saya</h1>

        <p class="mb-4 text-sm text-gray-500">
            Untuk mengajukan retur/komplain baru, buka detail pesanan yang berstatus "Selesai" lalu
            klik "Ajukan Retur/Komplain".
        </p>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">No. Pesanan</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3">Alasan</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Catatan Admin</th>
                        <th class="px-4 py-3">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="complaint in complaints.data" :key="complaint.id">
                        <td class="px-4 py-3 font-medium text-gray-900">
                            <Link :href="route('order.show', complaint.order_number)" class="text-indigo-600 hover:underline">
                                {{ complaint.order_number }}
                            </Link>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ complaint.type_label }}</td>
                        <td class="px-4 py-3 max-w-xs truncate text-gray-600">{{ complaint.reason }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ complaint.status_label }}</td>
                        <td class="px-4 py-3 max-w-xs truncate text-gray-600">{{ complaint.admin_note || '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ new Date(complaint.created_at).toLocaleDateString('id-ID') }}</td>
                    </tr>
                    <tr v-if="complaints.data.length === 0">
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada pengajuan retur/komplain.</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-1">
            <Link
                v-for="link in complaints.links"
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
    </CustomerLayout>
</template>
