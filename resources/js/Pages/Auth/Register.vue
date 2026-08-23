<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Daftar" />

    <GuestLayout>
        <h1 class="mb-6 text-lg font-semibold text-gray-900">Buat akun baru</h1>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="name" value="Nama Lengkap" />
                <TextInput id="name" v-model="form.name" autocomplete="name" required autofocus />
                <InputError :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />
                <TextInput id="email" v-model="form.email" type="email" autocomplete="username" required />
                <InputError :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Kata Sandi" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
                    required
                />
                <InputError :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Konfirmasi Kata Sandi" />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    required
                />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <PrimaryButton :disabled="form.processing">Daftar</PrimaryButton>

            <p class="text-center text-sm text-gray-600">
                Sudah punya akun?
                <Link href="/login" class="text-indigo-600 hover:underline">Masuk</Link>
            </p>
        </form>
    </GuestLayout>
</template>
