<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
    order_number: '',
    email: '',
});

const submit = () => {
    form.post(route('order.lookup.submit'));
};
</script>

<template>
    <Head title="Lacak Pesanan" />

    <StorefrontLayout>
        <div class="mx-auto max-w-md rounded-lg bg-white p-6 shadow">
            <h1 class="mb-4 text-lg font-semibold text-gray-900">Lacak Pesanan</h1>
            <p class="mb-4 text-sm text-gray-600">
                Masukkan nomor pesanan dan email yang digunakan saat checkout.
            </p>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel for="order_number" value="Nomor Pesanan" />
                    <TextInput id="order_number" v-model="form.order_number" placeholder="INV-20260101-ABCDEF" required autofocus />
                    <InputError :message="form.errors.order_number" />
                </div>

                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput id="email" v-model="form.email" type="email" required />
                    <InputError :message="form.errors.email" />
                </div>

                <PrimaryButton class="w-full" :disabled="form.processing">Cari Pesanan</PrimaryButton>
            </form>
        </div>
    </StorefrontLayout>
</template>
