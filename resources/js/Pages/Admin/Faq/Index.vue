<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

defineProps({
    entries: Array,
});

const destroy = (entry) => {
    if (confirm(`Hapus entri FAQ "${entry.question}"?`)) {
        router.delete(route('admin.faq.destroy', entry.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="FAQ Chatbot" />

    <AdminLayout>
        <template #title>FAQ Chatbot</template>

        <p class="mb-4 text-sm text-gray-500">
            Entri ini dipakai bot untuk membalas otomatis pertanyaan pelanggan di chat (sebelum staf sempat membalas)
            dan juga bisa ditampilkan sebagai halaman FAQ publik. Bot mencocokkan pesan pelanggan dengan kata kunci di
            bawah dan berhenti membalas otomatis begitu percakapan ditugaskan ke staf.
        </p>

        <div class="mb-4 flex justify-end">
            <Link :href="route('admin.faq.create')">
                <PrimaryButton>Tambah Entri FAQ</PrimaryButton>
            </Link>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Pertanyaan</th>
                        <th class="px-4 py-3">Kata Kunci</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="entry in entries" :key="entry.id">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ entry.question }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ entry.keywords }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ entry.category ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                :class="entry.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600'"
                            >
                                {{ entry.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="route('admin.faq.edit', entry.id)" class="mr-3 text-indigo-600 hover:underline">
                                Ubah
                            </Link>
                            <DangerButton @click="destroy(entry)">Hapus</DangerButton>
                        </td>
                    </tr>
                    <tr v-if="entries.length === 0">
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada entri FAQ.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
