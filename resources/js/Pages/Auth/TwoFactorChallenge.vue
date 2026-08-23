<script setup>
import { ref, computed, nextTick } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const recovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const toggleRecovery = async () => {
    recovery.value = !recovery.value;
    await nextTick();
    form.reset('code', 'recovery_code');
};

const submit = () => {
    form.post(route('two-factor.login'));
};

const description = computed(() =>
    recovery.value
        ? 'Masukkan salah satu kode pemulihan darurat Anda.'
        : 'Masukkan kode autentikasi dari aplikasi authenticator Anda.'
);
</script>

<template>
    <Head title="Verifikasi Dua Faktor" />

    <GuestLayout>
        <h1 class="mb-4 text-lg font-semibold text-gray-900">Verifikasi dua faktor</h1>
        <p class="mb-6 text-sm text-gray-600">{{ description }}</p>

        <form @submit.prevent="submit" class="space-y-4">
            <div v-if="!recovery">
                <InputLabel for="code" value="Kode" />
                <TextInput id="code" v-model="form.code" inputmode="numeric" autofocus autocomplete="one-time-code" />
                <InputError :message="form.errors.code" />
            </div>

            <div v-else>
                <InputLabel for="recovery_code" value="Kode Pemulihan" />
                <TextInput id="recovery_code" v-model="form.recovery_code" autocomplete="one-time-code" />
                <InputError :message="form.errors.recovery_code" />
            </div>

            <PrimaryButton class="w-full" :disabled="form.processing">Verifikasi</PrimaryButton>

            <button
                type="button"
                class="text-sm text-indigo-600 hover:underline"
                @click="toggleRecovery"
            >
                {{ recovery ? 'Gunakan kode autentikasi' : 'Gunakan kode pemulihan' }}
            </button>
        </form>
    </GuestLayout>
</template>
