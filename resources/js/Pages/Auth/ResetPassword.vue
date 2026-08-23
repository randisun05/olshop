<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Reset Kata Sandi" />

    <GuestLayout>
        <h1 class="mb-6 text-lg font-semibold text-gray-900">Buat kata sandi baru</h1>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput id="email" v-model="form.email" type="email" autocomplete="username" required />
                <InputError :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Kata Sandi Baru" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
                    required
                    autofocus
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

            <PrimaryButton :disabled="form.processing">Reset Kata Sandi</PrimaryButton>
        </form>
    </GuestLayout>
</template>
