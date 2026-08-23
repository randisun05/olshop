<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    coupon: Object,
});

const isEdit = computed(() => !!props.coupon);

const form = useForm({
    code: props.coupon?.code ?? '',
    type: props.coupon?.type ?? 'percent',
    value: props.coupon?.value ?? '',
    min_purchase: props.coupon?.min_purchase ?? 0,
    quota: props.coupon?.quota ?? '',
    expires_at: props.coupon?.expires_at ? props.coupon.expires_at.slice(0, 10) : '',
    is_active: props.coupon?.is_active ?? true,
});

const submit = () => {
    if (isEdit.value) {
        form.put(route('admin.coupons.update', props.coupon.id));
    } else {
        form.post(route('admin.coupons.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Ubah Kupon' : 'Tambah Kupon'" />

    <AdminLayout>
        <template #title>{{ isEdit ? 'Ubah Kupon' : 'Tambah Kupon' }}</template>

        <form @submit.prevent="submit" class="max-w-xl space-y-4 rounded-lg bg-white p-6 shadow">
            <div>
                <InputLabel for="code" value="Kode Kupon" />
                <TextInput id="code" v-model="form.code" required autofocus placeholder="mis. HEMAT10" />
                <InputError :message="form.errors.code" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="type" value="Tipe Diskon" />
                    <select
                        id="type"
                        v-model="form.type"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                        <option value="percent">Persentase (%)</option>
                        <option value="fixed">Nominal Tetap (Rp)</option>
                    </select>
                    <InputError :message="form.errors.type" />
                </div>
                <div>
                    <InputLabel for="value" :value="form.type === 'percent' ? 'Persentase (%)' : 'Nominal (Rp)'" />
                    <TextInput id="value" v-model="form.value" type="number" min="0" :max="form.type === 'percent' ? 100 : undefined" required />
                    <InputError :message="form.errors.value" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="min_purchase" value="Minimum Belanja (Rp)" />
                    <TextInput id="min_purchase" v-model="form.min_purchase" type="number" min="0" />
                    <InputError :message="form.errors.min_purchase" />
                </div>
                <div>
                    <InputLabel for="quota" value="Kuota Pemakaian (opsional)" />
                    <TextInput id="quota" v-model="form.quota" type="number" min="1" placeholder="Tanpa batas" />
                    <InputError :message="form.errors.quota" />
                </div>
            </div>

            <div>
                <InputLabel for="expires_at" value="Kedaluwarsa (opsional)" />
                <TextInput id="expires_at" v-model="form.expires_at" type="date" />
                <InputError :message="form.errors.expires_at" />
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300" />
                Aktif
            </label>

            <div class="flex items-center gap-3 pt-2">
                <PrimaryButton :disabled="form.processing">Simpan</PrimaryButton>
                <Link :href="route('admin.coupons.index')"><SecondaryButton type="button">Batal</SecondaryButton></Link>
            </div>
        </form>
    </AdminLayout>
</template>
