<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    complaints: Object,
    filters: Object,
});

const filterByStatus = (event) => {
    router.get(route('admin.complaints.index'), { status: event.target.value || undefined }, { preserveState: true });
};
</script>

<template>
    <Head title="Retur & Komplain" />

    <AdminLayout>
        <template #title>Retur & Komplain</template>

        <div class="mb-4">
            <select
                :value="filters.status ?? ''"
                @change="filterByStatus"
                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option value="">Semua Status</option>
                <option value="pending">Menunggu Peninjauan</option>
                <option value="processing">Sedang Diproses</option>
                <option value="resolved">Selesai</option>
                <option value="rejected">Ditolak</option>
            </select>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">No. Pesanan</th>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="complaint in complaints.data" :key="complaint.id">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ complaint.order_number }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ complaint.customer }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ complaint.type_label }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ complaint.status_label }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ new Date(complaint.created_at).toLocaleDateString('id-ID') }}</td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="route('admin.complaints.show', complaint.id)" class="text-indigo-600 hover:underline">
                                Detail
                            </Link>
                        </td>
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
    </AdminLayout>
</template>
