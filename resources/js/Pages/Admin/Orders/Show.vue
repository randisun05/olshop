<script setup>
import { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    order: Object,
});

const formatPrice = (value) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);

const shipForm = useForm({
    courier: '',
    tracking_number: '',
});

const markProcessing = () => {
    if (confirm('Tandai pesanan ini sedang diproses?')) {
        useForm({}).post(route('admin.orders.process', props.order.order_number), { preserveScroll: true });
    }
};

const markShipped = () => {
    shipForm.post(route('admin.orders.ship', props.order.order_number), { preserveScroll: true });
};

const markCompleted = () => {
    if (confirm('Tandai pesanan ini selesai?')) {
        useForm({}).post(route('admin.orders.complete', props.order.order_number), { preserveScroll: true });
    }
};

const cancelOrder = () => {
    if (confirm('Batalkan pesanan ini? Stok akan dikembalikan.')) {
        useForm({}).post(route('admin.orders.cancel', props.order.order_number), { preserveScroll: true });
    }
};
</script>

<template>
    <Head :title="`Pesanan ${order.order_number}`" />

    <AdminLayout>
        <template #title>Pesanan {{ order.order_number }}</template>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900">Status: {{ order.status_label }}</h2>
                        <span class="text-sm text-gray-500">{{ new Date(order.created_at).toLocaleString('id-ID') }}</span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                        <div>
                            <h3 class="font-medium text-gray-900">Pelanggan</h3>
                            <p class="text-gray-600">{{ order.customer_name }}<br>{{ order.customer_email }}</p>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Alamat Pengiriman</h3>
                            <p class="text-gray-600">
                                {{ order.recipient_name }} — {{ order.phone }}<br>
                                {{ order.address_line }}, {{ order.city }}
                            </p>
                        </div>
                    </div>

                    <p v-if="order.notes" class="mt-4 text-sm text-gray-600"><strong>Catatan:</strong> {{ order.notes }}</p>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-3 font-semibold text-gray-900">Item Pesanan</h2>
                    <div v-for="(item, i) in order.items" :key="i" class="flex justify-between border-b py-2 text-sm last:border-0">
                        <span>{{ item.product_name }} ({{ item.variant_label }}) x{{ item.quantity }}</span>
                        <span>{{ formatPrice(item.subtotal) }}</span>
                    </div>
                    <div class="mt-3 space-y-1 text-sm text-gray-600">
                        <div class="flex justify-between"><span>Subtotal</span><span>{{ formatPrice(order.subtotal) }}</span></div>
                        <div class="flex justify-between">
                            <span>Ongkos Kirim ({{ order.shipping_zone_name }})</span>
                            <span>{{ formatPrice(order.shipping_cost) }}</span>
                        </div>
                        <div class="flex justify-between text-base font-semibold text-gray-900">
                            <span>Total</span><span>{{ formatPrice(order.total) }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-3 font-semibold text-gray-900">Riwayat Status</h2>
                    <ol class="space-y-2 text-sm">
                        <li v-for="(h, i) in order.status_histories" :key="i" class="border-l-2 border-indigo-200 pl-3">
                            <span class="font-medium text-gray-900">{{ h.status_label }}</span>
                            <span class="text-gray-500"> — {{ new Date(h.created_at).toLocaleString('id-ID') }}</span>
                            <span v-if="h.changed_by" class="text-gray-500"> oleh {{ h.changed_by }}</span>
                            <p v-if="h.note" class="text-gray-600">{{ h.note }}</p>
                        </li>
                    </ol>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-3 font-semibold text-gray-900">Pembayaran</h2>
                    <p class="text-sm text-gray-600">{{ order.payment?.method_label }}</p>
                    <p class="text-sm text-gray-600">Status: {{ order.payment?.status_label }}</p>
                    <a v-if="order.payment?.proof_url" :href="order.payment.proof_url" target="_blank" class="mt-2 inline-block text-sm text-indigo-600 hover:underline">
                        Lihat Bukti Transfer
                    </a>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-3 font-semibold text-gray-900">Aksi</h2>

                    <div v-if="order.status === 'paid'">
                        <PrimaryButton class="w-full" @click="markProcessing">Tandai Diproses</PrimaryButton>
                    </div>

                    <form v-else-if="order.status === 'processing'" @submit.prevent="markShipped" class="space-y-3">
                        <div>
                            <InputLabel for="courier" value="Kurir" />
                            <TextInput id="courier" v-model="shipForm.courier" placeholder="mis. JNE, J&T" required />
                            <InputError :message="shipForm.errors.courier" />
                        </div>
                        <div>
                            <InputLabel for="tracking_number" value="Nomor Resi" />
                            <TextInput id="tracking_number" v-model="shipForm.tracking_number" required />
                            <InputError :message="shipForm.errors.tracking_number" />
                        </div>
                        <PrimaryButton class="w-full" :disabled="shipForm.processing">Tandai Dikirim</PrimaryButton>
                    </form>

                    <div v-else-if="order.status === 'shipped'">
                        <p class="mb-3 text-sm text-gray-600">
                            {{ order.shipment?.courier }} — {{ order.shipment?.tracking_number }}
                        </p>
                        <PrimaryButton class="w-full" @click="markCompleted">Tandai Selesai</PrimaryButton>
                    </div>

                    <p v-else class="text-sm text-gray-500">Tidak ada aksi tersedia untuk status ini.</p>

                    <DangerButton
                        v-if="!['shipped', 'completed', 'cancelled'].includes(order.status)"
                        class="mt-3 w-full justify-center"
                        @click="cancelOrder"
                    >
                        Batalkan Pesanan
                    </DangerButton>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
