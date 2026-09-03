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
    email: '',
    password: '',
    remember: false,
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
            window.grecaptcha.execute(siteKey, { action: 'login' }).then((token) => {
                form.recaptcha_token = token;
                form.post(route('login'), {
                    onFinish: () => form.reset('password'),
                });
            });
        });
        return;
    }

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

            <PrimaryButton class="w-full" :disabled="form.processing">Masuk</PrimaryButton>

            <p class="text-center text-sm text-gray-600">
                Belum punya akun?
                <Link href="/register" class="text-indigo-600 hover:underline">Daftar</Link>
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
                Masuk dengan {{ providerLabels[provider] ?? provider }}
            </a>
        </template>
    </GuestLayout>
</template>
