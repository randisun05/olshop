<script setup>
import { computed, ref } from 'vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    twoFactorEnabled: Boolean,
    twoFactorRequired: Boolean,
    canDeleteOwnAccount: Boolean,
});

const page = usePage();
const roles = page.props.auth.user?.roles ?? [];
const isAdminArea = computed(() => roles.some((r) => ['Super Admin', 'Admin', 'Staff Gudang', 'Staff CS'].includes(r)));
const Layout = computed(() => (isAdminArea.value ? AdminLayout : CustomerLayout));

const setupData = ref(null); // { svg, secretKey }
const showRecoveryCodes = ref(false);
const recoveryCodes = ref([]);

const confirmForm = useForm({ code: '' });

const enableTwoFactor = () => {
    router.post(route('two-factor.enable'), {}, {
        preserveScroll: true,
        onSuccess: () => loadSetupData(),
    });
};

const loadSetupData = async () => {
    const [qr, secret] = await Promise.all([
        fetch(route('two-factor.qr-code')).then((r) => r.json()),
        fetch(route('two-factor.secret-key')).then((r) => r.json()),
    ]);

    if (qr.svg) {
        setupData.value = { svg: qr.svg, secretKey: secret.secretKey };
    }
};

const confirmTwoFactor = () => {
    confirmForm.post(route('two-factor.confirm'), {
        preserveScroll: true,
        onSuccess: () => {
            setupData.value = null;
            confirmForm.reset();
        },
    });
};

const disableTwoFactor = () => {
    if (!confirm('Nonaktifkan autentikasi dua faktor?')) return;

    router.delete(route('two-factor.disable'), { preserveScroll: true });
};

const loadRecoveryCodes = async () => {
    recoveryCodes.value = await fetch(route('two-factor.recovery-codes')).then((r) => r.json());
    showRecoveryCodes.value = true;
};

const showDeleteForm = ref(false);
const deleteForm = useForm({ password: '' });

const deleteAccount = () => {
    deleteForm.delete(route('account.security.destroy-account'), { preserveScroll: true });
};
</script>

<template>
    <Head title="Keamanan Akun" />

    <component :is="Layout">
        <template #title>Keamanan Akun</template>

        <div class="max-w-xl space-y-6 rounded-lg bg-white p-6 shadow">
            <div>
                <h2 class="text-sm font-semibold text-gray-700">Autentikasi Dua Faktor (2FA)</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Tambahkan lapisan keamanan ekstra menggunakan aplikasi authenticator (mis. Google
                    Authenticator, Authy).
                    <span v-if="twoFactorRequired" class="font-medium text-amber-600">
                        Wajib diaktifkan untuk akun Admin/Super Admin.
                    </span>
                </p>
            </div>

            <div v-if="!twoFactorEnabled && !setupData">
                <PrimaryButton @click="enableTwoFactor">Aktifkan 2FA</PrimaryButton>
                <p class="mt-2 text-xs text-gray-500">
                    Jika diminta konfirmasi kata sandi, isi lalu klik "Aktifkan 2FA" sekali lagi.
                </p>
            </div>

            <div v-else-if="setupData" class="space-y-4">
                <p class="text-sm text-gray-700">Pindai kode QR ini dengan aplikasi authenticator Anda:</p>
                <div class="inline-block rounded border p-2" v-html="setupData.svg"></div>
                <p class="text-xs text-gray-500">Atau masukkan kunci manual: <code class="rounded bg-gray-100 px-1">{{ setupData.secretKey }}</code></p>

                <form @submit.prevent="confirmTwoFactor" class="flex items-end gap-3">
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700">Kode 6 Digit</label>
                        <input
                            id="code"
                            v-model="confirmForm.code"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            class="mt-1 block w-40 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                        <InputError :message="confirmForm.errors.code" />
                    </div>
                    <PrimaryButton :disabled="confirmForm.processing">Konfirmasi</PrimaryButton>
                </form>
            </div>

            <div v-else class="space-y-3">
                <p class="text-sm font-medium text-green-700">2FA aktif untuk akun ini.</p>
                <div class="flex gap-3">
                    <SecondaryButton @click="loadRecoveryCodes">Lihat Kode Pemulihan</SecondaryButton>
                    <DangerButton @click="disableTwoFactor">Nonaktifkan 2FA</DangerButton>
                </div>
                <div v-if="showRecoveryCodes" class="rounded bg-gray-50 p-3 font-mono text-xs">
                    <p v-for="rc in recoveryCodes" :key="rc">{{ rc }}</p>
                </div>
            </div>
        </div>

        <div v-if="canDeleteOwnAccount" class="mt-6 max-w-xl space-y-3 rounded-lg border border-red-200 bg-white p-6 shadow">
            <h2 class="text-sm font-semibold text-red-700">Hapus Akun</h2>
            <p class="text-sm text-gray-500">
                Menghapus akun bersifat permanen: alamat, wishlist, dan riwayat chat Anda akan ikut terhapus.
                Riwayat pesanan tetap kami simpan untuk keperluan pembukuan, tapi tidak lagi terhubung ke akun Anda.
            </p>

            <SecondaryButton v-if="!showDeleteForm" @click="showDeleteForm = true">Hapus Akun Saya</SecondaryButton>

            <form v-else @submit.prevent="deleteAccount" class="space-y-3">
                <div>
                    <label for="delete_password" class="block text-sm font-medium text-gray-700">
                        Masukkan kata sandi untuk konfirmasi
                    </label>
                    <input
                        id="delete_password"
                        v-model="deleteForm.password"
                        type="password"
                        autocomplete="current-password"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                    />
                    <InputError :message="deleteForm.errors.password" />
                </div>
                <div class="flex gap-3">
                    <DangerButton :disabled="deleteForm.processing">Hapus Akun Permanen</DangerButton>
                    <SecondaryButton type="button" @click="showDeleteForm = false; deleteForm.reset()">Batal</SecondaryButton>
                </div>
            </form>
        </div>
    </component>
</template>
