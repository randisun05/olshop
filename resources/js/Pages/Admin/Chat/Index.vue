<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    conversations: Object,
    filters: Object,
});

const filterByStatus = (event) => {
    router.get(route('admin.chat.index'), { status: event.target.value || undefined }, { preserveState: true });
};
</script>

<template>
    <Head title="Chat" />

    <AdminLayout>
        <template #title>Chat Pelanggan</template>

        <div class="mb-4">
            <select
                :value="filters.status ?? ''"
                @change="filterByStatus"
                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option value="">Semua Status</option>
                <option value="open">Terbuka</option>
                <option value="closed">Ditutup</option>
            </select>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Subjek</th>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3">Ditangani</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Pesan Terakhir</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="conversation in conversations.data" :key="conversation.id">
                        <td class="px-4 py-3 font-medium text-gray-900">
                            {{ conversation.subject }}
                            <span
                                v-if="conversation.unread > 0"
                                class="ml-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-xs font-semibold text-white"
                            >
                                {{ conversation.unread }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ conversation.customer }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ conversation.assigned_to ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ conversation.status_label }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ conversation.last_message_at ? new Date(conversation.last_message_at).toLocaleString('id-ID') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="route('admin.chat.show', conversation.id)" class="text-indigo-600 hover:underline">
                                Buka
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="conversations.data.length === 0">
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada obrolan.</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-1">
            <Link
                v-for="link in conversations.links"
                :key="link.label"
                :href="link.url ?? '#'"
                v-html="link.label"
                class="rounded px-3 py-1 text-sm"
                :class="[
                    link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50',
                    !link.url && 'pointer-events-none opacity-50',
                ]"
            />
        </div>
    </AdminLayout>
</template>
