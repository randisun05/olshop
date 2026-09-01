<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    complaint: Object,
});

const form = useForm({
    status: props.complaint.status === 'pending' ? 'processing' : props.complaint.status,
    admin_note: props.complaint.admin_note ?? '',
});

const submit = () => {
    form.post(route('admin.complaints.respond', props.complaint.id));
};
</script>

<template>
    <Head :title="`Komplain #${complaint.id}`" />

    <AdminLayout>
        <template #title>Detail Retur/Komplain</template>

        <div class="max-w-2xl space-y-6">
            <div class="rounded-lg bg-white p-6 shadow">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">No. Pesanan</dt>
                        <dd class="font-medium text-gray-900">{{ complaint.order_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Pelanggan</dt>
                        <dd class="font-medium text-gray-900">{{ complaint.customer }} ({{ complaint.customer_email }})</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Tipe</dt>
                        <dd class="text-gray-900">{{ complaint.type_label }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Status Saat Ini</dt>
                        <dd class="text-gray-900">{{ complaint.status_label }}</dd>
                    </div>
                    <div v-if="complaint.order_item" class="col-span-2">
                        <dt class="text-gray-500">Item Terkait</dt>
                        <dd class="text-gray-900">{{ complaint.order_item.product_name }} ({{ complaint.order_item.variant_label }})</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-gray-500">Alasan</dt>
                        <dd class="whitespace-pre-line text-gray-900">{{ complaint.reason }}</dd>
                    </div>
                    <div v-if="complaint.image_url" class="col-span-2">
                        <dt class="mb-1 text-gray-500">Foto Bukti</dt>
                        <img :src="complaint.image_url" class="max-w-xs rounded border" />
                    </div>
                </dl>
            </div>

            <form @submit.prevent="submit" class="space-y-4 rounded-lg bg-white p-6 shadow">
                <h2 class="font-semibold text-gray-900">Tanggapi Pengajuan</h2>
                <div>
                    <InputLabel for="status" value="Status Baru" />
                    <select
                        id="status"
                        v-model="form.status"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                        <option value="processing">Sedang Diproses</option>
                        <option value="resolved">Selesai</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                    <InputError :message="form.errors.status" />
                </div>
                <div>
                    <InputLabel for="admin_note" value="Catatan untuk Pelanggan (opsional)" />
                    <textarea
                        id="admin_note"
                        v-model="form.admin_note"
                        rows="3"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    ></textarea>
                    <InputError :message="form.errors.admin_note" />
                </div>
                <PrimaryButton :disabled="form.processing">Simpan & Kirim Notifikasi</PrimaryButton>
            </form>
        </div>
    </AdminLayout>
</template>
