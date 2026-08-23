<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Lupa Kata Sandi" />

    <GuestLayout>
        <h1 class="mb-4 text-lg font-semibold text-gray-900">Lupa kata sandi</h1>
        <p class="mb-6 text-sm text-gray-600">
            Masukkan email Anda, kami akan mengirimkan tautan untuk membuat kata sandi baru.
        </p>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">{{ status }}</div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput id="email" v-model="form.email" type="email" autocomplete="username" required autofocus />
                <InputError :message="form.errors.email" />
            </div>

            <PrimaryButton class="w-full" :disabled="form.processing">Kirim Tautan Reset</PrimaryButton>
        </form>
    </GuestLayout>
</template>
