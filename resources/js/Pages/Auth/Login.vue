<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk" />

    <GuestLayout>
        <h1 class="mb-6 text-lg font-semibold text-gray-900">Masuk ke akun Anda</h1>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    autocomplete="username"
                    required
                    autofocus
                />
                <InputError :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Kata Sandi" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    autocomplete="current-password"
                    required
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2">
                    <input type="checkbox" v-model="form.remember" class="rounded border-gray-300" />
                    Ingat saya
                </label>
                <Link href="/forgot-password" class="text-indigo-600 hover:underline">
                    Lupa kata sandi?
                </Link>
            </div>

            <PrimaryButton :disabled="form.processing">Masuk</PrimaryButton>

            <p class="text-center text-sm text-gray-600">
                Belum punya akun?
                <Link href="/register" class="text-indigo-600 hover:underline">Daftar</Link>
            </p>
        </form>
    </GuestLayout>
</template>
