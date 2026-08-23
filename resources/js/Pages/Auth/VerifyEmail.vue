<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    status: String,
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <Head title="Verifikasi Email" />

    <GuestLayout>
        <h1 class="mb-4 text-lg font-semibold text-gray-900">Verifikasi email Anda</h1>
        <p class="mb-6 text-sm text-gray-600">
            Terima kasih telah mendaftar. Sebelum melanjutkan, mohon verifikasi email Anda dengan
            mengklik tautan yang baru saja kami kirimkan. Jika belum menerima email, kami akan
            mengirimkan yang baru.
        </p>

        <div v-if="verificationLinkSent" class="mb-4 text-sm font-medium text-green-600">
            Tautan verifikasi baru telah dikirim ke alamat email Anda.
        </div>

        <form @submit.prevent="submit" class="flex items-center justify-between">
            <PrimaryButton :disabled="form.processing">Kirim Ulang Email Verifikasi</PrimaryButton>
            <Link href="/logout" method="post" as="button" class="text-sm text-gray-600 hover:underline">
                Keluar
            </Link>
        </form>
    </GuestLayout>
</template>
