<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    order: Object,
});

const form = useForm({
    order_item_id: '',
    type: 'retur',
    reason: '',
    image: null,
});

const submit = () => {
    form.post(route('customer.complaints.store', props.order.order_number), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Ajukan Retur/Komplain" />

    <CustomerLayout>
        <h1 class="mb-6 text-lg font-semibold text-gray-900">Ajukan Retur/Komplain — Pesanan {{ order.order_number }}</h1>

        <form @submit.prevent="submit" class="max-w-xl space-y-4 rounded-lg bg-white p-6 shadow">
            <div>
                <InputLabel for="type" value="Jenis Pengajuan" />
                <select
                    id="type"
                    v-model="form.type"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
                    <option value="retur">Retur/Pengembalian</option>
                    <option value="komplain">Komplain Lainnya</option>
                </select>
                <InputError :message="form.errors.type" />
            </div>

            <div>
                <InputLabel for="order_item_id" value="Item Terkait (opsional)" />
                <select
                    id="order_item_id"
                    v-model="form.order_item_id"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
                    <option value="">Seluruh Pesanan</option>
                    <option v-for="item in order.items" :key="item.id" :value="item.id">
                        {{ item.product_name }} ({{ item.variant_label }})
                    </option>
                </select>
                <InputError :message="form.errors.order_item_id" />
            </div>

            <div>
                <InputLabel for="reason" value="Alasan / Deskripsi Masalah" />
                <textarea
                    id="reason"
                    v-model="form.reason"
                    rows="4"
                    required
                    placeholder="Jelaskan masalah yang Anda alami secara detail..."
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                ></textarea>
                <InputError :message="form.errors.reason" />
            </div>

            <div>
                <InputLabel for="image" value="Foto Bukti (opsional)" />
                <input id="image" type="file" accept="image/*" @change="form.image = $event.target.files[0]" class="block text-sm" />
                <InputError :message="form.errors.image" />
            </div>

            <div class="flex items-center gap-3 pt-2">
                <PrimaryButton :disabled="form.processing">Kirim Pengajuan</PrimaryButton>
                <Link :href="route('order.show', order.order_number)"><SecondaryButton type="button">Batal</SecondaryButton></Link>
            </div>
        </form>
    </CustomerLayout>
</template>
