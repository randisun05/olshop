<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    settings: Object,
});

const form = useForm({
    store_name: props.settings.store_name ?? '',
    store_email: props.settings.store_email ?? '',
    store_phone: props.settings.store_phone ?? '',
    store_address: props.settings.store_address ?? '',
    bank_name: props.settings.bank_name ?? '',
    bank_account_number: props.settings.bank_account_number ?? '',
    bank_account_holder: props.settings.bank_account_holder ?? '',
    tax_percent: props.settings.tax_percent ?? '',
    low_stock_threshold: props.settings.low_stock_threshold ?? 5,
});

const submit = () => {
    form.put(route('admin.settings.update'));
};
</script>

<template>
    <Head title="Pengaturan Toko" />

    <AdminLayout>
        <template #title>Pengaturan Toko</template>

        <form @submit.prevent="submit" class="max-w-2xl space-y-6 rounded-lg bg-white p-6 shadow">
            <div>
                <h2 class="mb-3 text-sm font-semibold text-gray-700">Identitas Toko</h2>
                <div class="space-y-4">
                    <div>
                        <InputLabel for="store_name" value="Nama Toko" />
                        <TextInput id="store_name" v-model="form.store_name" required autofocus />
                        <InputError :message="form.errors.store_name" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="store_email" value="Email Kontak" />
                            <TextInput id="store_email" v-model="form.store_email" type="email" />
                            <InputError :message="form.errors.store_email" />
                        </div>
                        <div>
                            <InputLabel for="store_phone" value="Telepon" />
                            <TextInput id="store_phone" v-model="form.store_phone" />
                            <InputError :message="form.errors.store_phone" />
                        </div>
                    </div>
                    <div>
                        <InputLabel for="store_address" value="Alamat" />
                        <textarea
                            id="store_address"
                            v-model="form.store_address"
                            rows="3"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        ></textarea>
                        <InputError :message="form.errors.store_address" />
                    </div>
                </div>
            </div>

            <div>
                <h2 class="mb-3 text-sm font-semibold text-gray-700">Rekening Pembayaran (Transfer Manual)</h2>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <InputLabel for="bank_name" value="Nama Bank" />
                        <TextInput id="bank_name" v-model="form.bank_name" />
                        <InputError :message="form.errors.bank_name" />
                    </div>
                    <div>
                        <InputLabel for="bank_account_number" value="Nomor Rekening" />
                        <TextInput id="bank_account_number" v-model="form.bank_account_number" />
                        <InputError :message="form.errors.bank_account_number" />
                    </div>
                    <div>
                        <InputLabel for="bank_account_holder" value="Atas Nama" />
                        <TextInput id="bank_account_holder" v-model="form.bank_account_holder" />
                        <InputError :message="form.errors.bank_account_holder" />
                    </div>
                </div>
            </div>

            <div>
                <h2 class="mb-3 text-sm font-semibold text-gray-700">Lainnya</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="tax_percent" value="Pajak (%)" />
                        <TextInput id="tax_percent" v-model="form.tax_percent" type="number" min="0" max="100" step="0.01" />
                        <InputError :message="form.errors.tax_percent" />
                    </div>
                    <div>
                        <InputLabel for="low_stock_threshold" value="Ambang Batas Stok Menipis" />
                        <TextInput id="low_stock_threshold" v-model="form.low_stock_threshold" type="number" min="0" />
                        <InputError :message="form.errors.low_stock_threshold" />
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <PrimaryButton :disabled="form.processing">Simpan Pengaturan</PrimaryButton>
            </div>
        </form>
    </AdminLayout>
</template>
