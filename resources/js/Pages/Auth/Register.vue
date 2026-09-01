<script setup>
import { onMounted } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const page = usePage();
const siteKey = page.props.recaptchaSiteKey;
const socialProviders = page.props.socialProviders ?? [];
const providerLabels = { google: 'Google' };

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    recaptcha_token: '',
});

onMounted(() => {
    if (!siteKey) return;

    const script = document.createElement('script');
    script.src = `https://www.google.com/recaptcha/api.js?render=${siteKey}`;
    document.head.appendChild(script);
});

const submit = () => {
    if (siteKey && window.grecaptcha) {
        window.grecaptcha.ready(() => {
            window.grecaptcha.execute(siteKey, { action: 'register' }).then((token) => {
                form.recaptcha_token = token;
                form.post(route('register'), {
                    onFinish: () => form.reset('password', 'password_confirmation'),
                });
            });
        });
        return;
    }

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

            <PrimaryButton class="w-full" :disabled="form.processing">Daftar</PrimaryButton>

            <p class="text-center text-sm text-gray-600">
                Sudah punya akun?
                <Link href="/login" class="text-indigo-600 hover:underline">Masuk</Link>
            </p>
        </form>

        <template v-if="socialProviders.length">
            <div class="my-4 flex items-center gap-3 text-xs text-gray-400">
                <span class="h-px flex-1 bg-gray-200"></span>
                atau
                <span class="h-px flex-1 bg-gray-200"></span>
            </div>
            <a
                v-for="provider in socialProviders"
                :key="provider"
                :href="route('social.redirect', provider)"
                class="block w-full rounded-md border border-gray-300 px-4 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                Daftar dengan {{ providerLabels[provider] ?? provider }}
            </a>
        </template>
    </GuestLayout>
</template>
