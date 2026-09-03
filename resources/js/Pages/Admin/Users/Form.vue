<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    roles: Array,
    user: Object,
});

const isEdit = computed(() => !!props.user);

const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    password: '',
    password_confirmation: '',
    role: props.user?.role ?? '',
});

const submit = () => {
    if (isEdit.value) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(route('admin.users.update', props.user.id));
    } else {
        form.post(route('admin.users.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Ubah Akun Staf' : 'Tambah Akun Staf'" />

    <AdminLayout>
        <template #title>{{ isEdit ? 'Ubah Akun Staf' : 'Tambah Akun Staf' }}</template>

        <form @submit.prevent="submit" class="max-w-xl space-y-4 rounded-lg bg-white p-6 shadow">
            <div>
                <InputLabel for="name" value="Nama" />
                <TextInput id="name" v-model="form.name" required autofocus />
                <InputError :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />
                <TextInput id="email" v-model="form.email" type="email" required />
                <InputError :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="role" value="Role" />
                <select id="role" v-model="form.role" required class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                    <option value="" disabled>Pilih role</option>
                    <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                </select>
                <InputError :message="form.errors.role" />
            </div>

            <div>
                <InputLabel for="password" :value="isEdit ? 'Password Baru (opsional)' : 'Password'" />
                <TextInput id="password" v-model="form.password" type="password" :required="!isEdit" autocomplete="new-password" />
                <InputError :message="form.errors.password" />
                <p v-if="isEdit" class="mt-1 text-xs text-gray-500">Kosongkan jika tidak ingin mengubah password.</p>
            </div>

            <div v-if="form.password">
                <InputLabel for="password_confirmation" value="Konfirmasi Password" />
                <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" autocomplete="new-password" />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <div class="flex items-center gap-3 pt-2">
                <PrimaryButton :disabled="form.processing">Simpan</PrimaryButton>
                <Link :href="route('admin.users.index')"><SecondaryButton type="button">Batal</SecondaryButton></Link>
            </div>
        </form>
    </AdminLayout>
</template>
