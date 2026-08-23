<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Konfirmasi Kata Sandi" />

    <GuestLayout>
        <h1 class="mb-4 text-lg font-semibold text-gray-900">Konfirmasi kata sandi</h1>
        <p class="mb-6 text-sm text-gray-600">
            Ini adalah area yang aman. Mohon konfirmasi kata sandi Anda sebelum melanjutkan.
        </p>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="password" value="Kata Sandi" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    autocomplete="current-password"
                    required
                    autofocus
                />
                <InputError :message="form.errors.password" />
            </div>

            <PrimaryButton :disabled="form.processing">Konfirmasi</PrimaryButton>
        </form>
    </GuestLayout>
</template>
