<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

defineProps({
    conversations: Object,
});
</script>

<template>
    <Head title="Chat CS" />

    <CustomerLayout>
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-lg font-semibold text-gray-900">Chat dengan Customer Service</h1>
            <Link
                :href="route('customer.chat.create')"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
            >
                + Mulai Obrolan Baru
            </Link>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <ul class="divide-y divide-gray-100">
                <li v-for="conversation in conversations.data" :key="conversation.id">
                    <Link :href="route('customer.chat.show', conversation.id)" class="flex items-center justify-between px-4 py-4 hover:bg-gray-50">
                        <div>
                            <p class="font-medium text-gray-900">{{ conversation.subject }}</p>
                            <p class="text-xs text-gray-500">{{ conversation.status_label }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span
                                v-if="conversation.unread > 0"
                                class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-xs font-semibold text-white"
                            >
                                {{ conversation.unread }}
                            </span>
                            <span v-if="conversation.last_message_at" class="text-xs text-gray-400">
                                {{ new Date(conversation.last_message_at).toLocaleString('id-ID') }}
                            </span>
                        </div>
                    </Link>
                </li>
                <li v-if="conversations.data.length === 0" class="px-4 py-6 text-center text-sm text-gray-500">
                    Belum ada obrolan. Klik "Mulai Obrolan Baru" untuk menghubungi CS.
                </li>
            </ul>
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
    </CustomerLayout>
</template>
