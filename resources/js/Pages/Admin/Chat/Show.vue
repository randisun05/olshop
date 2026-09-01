<script setup>
import { nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    conversation: Object,
    messages: Array,
});

const messages = ref([...props.messages]);
const statusLabel = ref(props.conversation.status_label);
const body = ref('');
const sending = ref(false);
const scrollBox = ref(null);

const csrfToken = () => document.querySelector('meta[name="csrf-token"]').content;

const scrollToBottom = () => {
    nextTick(() => {
        if (scrollBox.value) scrollBox.value.scrollTop = scrollBox.value.scrollHeight;
    });
};

const lastId = () => (messages.value.length ? messages.value[messages.value.length - 1].id : 0);

const poll = async () => {
    const response = await fetch(route('admin.chat.poll', props.conversation.id) + `?after_id=${lastId()}`, {
        headers: { Accept: 'application/json' },
    });
    const data = await response.json();
    statusLabel.value = data.status_label;
    if (data.messages.length) {
        messages.value.push(...data.messages);
        scrollToBottom();
    }
};

let interval = null;

onMounted(() => {
    scrollToBottom();
    interval = setInterval(poll, 4000);
});

onUnmounted(() => {
    if (interval) clearInterval(interval);
});

const sendMessage = async () => {
    if (!body.value.trim() || sending.value) return;

    sending.value = true;
    try {
        const response = await fetch(route('admin.chat.message', props.conversation.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({ body: body.value }),
        });
        const data = await response.json();
        messages.value.push(data.message);
        body.value = '';
        scrollToBottom();
    } finally {
        sending.value = false;
    }
};

const toggleStatus = () => {
    const routeName = props.conversation.status === 'open' ? 'admin.chat.close' : 'admin.chat.reopen';
    router.post(route(routeName, props.conversation.id), {}, { preserveScroll: true });
};

watch(messages, scrollToBottom, { deep: true });
</script>

<template>
    <Head :title="conversation.subject" />

    <AdminLayout>
        <template #title>Chat — {{ conversation.subject }}</template>

        <Link :href="route('admin.chat.index')" class="mb-4 inline-block text-sm text-indigo-600 hover:underline">
            &larr; Kembali ke Daftar Chat
        </Link>

        <div class="flex h-[32rem] flex-col rounded-lg bg-white shadow">
            <div class="flex items-center justify-between border-b px-4 py-3">
                <div>
                    <p class="text-xs text-gray-500">Pelanggan: {{ conversation.customer }}</p>
                    <p v-if="conversation.order_number" class="text-xs text-gray-500">Terkait pesanan {{ conversation.order_number }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">{{ statusLabel }}</span>
                    <SecondaryButton type="button" @click="toggleStatus">
                        {{ conversation.status === 'open' ? 'Tutup' : 'Buka Kembali' }}
                    </SecondaryButton>
                </div>
            </div>

            <div ref="scrollBox" class="flex-1 space-y-3 overflow-y-auto p-4">
                <div v-for="message in messages" :key="message.id" class="flex" :class="message.is_mine ? 'justify-end' : 'justify-start'">
                    <div
                        class="max-w-xs rounded-lg px-3 py-2 text-sm"
                        :class="message.is_mine ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-800'"
                    >
                        <p v-if="!message.is_mine" class="mb-0.5 text-xs font-semibold opacity-75">{{ message.sender_name }}</p>
                        <p class="whitespace-pre-line">{{ message.body }}</p>
                        <p class="mt-1 text-right text-xs opacity-60">{{ new Date(message.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}</p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="sendMessage" class="flex items-center gap-2 border-t p-3">
                <input
                    v-model="body"
                    type="text"
                    placeholder="Tulis balasan..."
                    class="flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
                <PrimaryButton :disabled="sending || !body.trim()">Kirim</PrimaryButton>
            </form>
        </div>
    </AdminLayout>
</template>
