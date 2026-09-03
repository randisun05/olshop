<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    users: Array,
});

const toggleActive = (user) => {
    if (user.is_self) return;

    const message = user.is_active
        ? `Nonaktifkan akun "${user.name}"? Akun ini tidak akan bisa login sampai diaktifkan kembali.`
        : `Aktifkan kembali akun "${user.name}"?`;

    if (confirm(message)) {
        router.post(route('admin.users.toggle-active', user.id), {}, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Pengguna Staf" />

    <AdminLayout>
        <template #title>Pengguna Staf</template>

        <div class="mb-4 flex justify-end">
            <Link :href="route('admin.users.create')">
                <PrimaryButton>Tambah Akun Staf</PrimaryButton>
            </Link>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="user in users" :key="user.id">
                        <td class="px-4 py-3 font-medium text-gray-900">
                            {{ user.name }}
                            <span v-if="user.is_self" class="ml-1 text-xs text-gray-400">(Anda)</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ user.email }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ user.role }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                :class="user.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600'"
                            >
                                {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="route('admin.users.edit', user.id)" class="mr-3 text-indigo-600 hover:underline">
                                Ubah
                            </Link>
                            <button
                                type="button"
                                class="text-sm hover:underline"
                                :class="user.is_self ? 'cursor-not-allowed text-gray-300' : 'text-red-600'"
                                :disabled="user.is_self"
                                @click="toggleActive(user)"
                            >
                                {{ user.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </td>
                    </tr>
                    <tr v-if="users.length === 0">
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada akun staf.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
