<script setup>
import { onMounted, reactive, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    order: Object,
    email: String,
    isOwner: Boolean,
    canFileComplaint: Boolean,
    midtrans: Object,
});

const formatPrice = (value) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);

const snapReady = ref(false);

onMounted(() => {
    if (props.order.payment?.method === 'midtrans' && props.order.payment.status === 'pending') {
        const script = document.createElement('script');
        script.src = props.midtrans.isProduction
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
        script.setAttribute('data-client-key', props.midtrans.clientKey);
        script.onload = () => (snapReady.value = true);
        document.head.appendChild(script);
    }
});

const payNow = () => {
    window.snap.pay(props.order.payment.provider_reference, {
        onSuccess: () => window.location.reload(),
        onPending: () => window.location.reload(),
    });
};

const proofForm = useForm({ proof: null });

const submitProof = () => {
    proofForm.post(route('order.proof', { order: props.order.order_number, email: props.email }), {
        forceFormData: true,
    });
};

const confirmForm = useForm({});

const confirmReceived = () => {
    if (confirm('Konfirmasi bahwa pesanan sudah Anda terima?')) {
        confirmForm.post(route('customer.orders.confirm', props.order.order_number));
    }
};

const reviewForms = reactive({});
const openReviewFor = ref(null);

const startReview = (item) => {
    reviewForms[item.id] = useForm({ rating: 5, comment: '' });
    openReviewFor.value = item.id;
};

const submitReview = (item) => {
    reviewForms[item.id].post(route('customer.reviews.store', item.id), {
        onSuccess: () => (openReviewFor.value = null),
    });
};
</script>

<template>
    <Head :title="`Pesanan ${order.order_number}`" />

    <StorefrontLayout>
        <div class="mx-auto max-w-3xl space-y-6">
            <div class="rounded-lg bg-white p-6 shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-lg font-semibold text-gray-900">Pesanan {{ order.order_number }}</h1>
                        <p class="text-sm text-gray-500">{{ new Date(order.created_at).toLocaleString('id-ID') }}</p>
                    </div>
                    <span class="rounded-full bg-indigo-100 px-3 py-1 text-sm font-medium text-indigo-700">
                        {{ order.status_label }}
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <h3 class="font-medium text-gray-900">Alamat Pengiriman</h3>
                        <p class="text-gray-600">
                            {{ order.recipient_name }} — {{ order.phone }}<br>
                            {{ order.address_line }}, {{ order.city }}
                        </p>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">Pembayaran</h3>
                        <p class="text-gray-600">
                            {{ order.payment?.method_label }}<br>
                            Status: {{ order.payment?.status_label }}
                        </p>
                    </div>
                    <div v-if="order.shipment">
                        <h3 class="font-medium text-gray-900">Pengiriman</h3>
                        <p class="text-gray-600">
                            {{ order.shipment.courier }}<br>
                            Resi: {{ order.shipment.tracking_number }}
                        </p>
                    </div>
                </div>
            </div>

            <div v-if="order.status === 'shipped' && isOwner" class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-1 font-semibold text-gray-900">Pesanan Sudah Dikirim</h2>
                <p class="mb-4 text-sm text-gray-600">Sudah menerima paketnya? Konfirmasi di bawah ini.</p>
                <PrimaryButton :disabled="confirmForm.processing" @click="confirmReceived">
                    Konfirmasi Pesanan Diterima
                </PrimaryButton>
            </div>

            <div v-if="canFileComplaint" class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-1 font-semibold text-gray-900">Ada Masalah dengan Pesanan Ini?</h2>
                <p class="mb-4 text-sm text-gray-600">Ajukan retur atau komplain jika produk tidak sesuai, rusak, atau ada kendala lain.</p>
                <Link :href="route('customer.complaints.create', order.order_number)">
                    <PrimaryButton type="button">Ajukan Retur/Komplain</PrimaryButton>
                </Link>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-3 font-semibold text-gray-900">Item Pesanan</h2>
                <div v-for="(item, i) in order.items" :key="i" class="border-b py-3 text-sm last:border-0">
                    <div class="flex justify-between">
                        <span>{{ item.product_name }} ({{ item.variant_label }}) x{{ item.quantity }}</span>
                        <span>{{ formatPrice(item.subtotal) }}</span>
                    </div>

                    <div v-if="item.reviewed" class="mt-1 text-xs text-green-600">✓ Sudah diulas</div>

                    <button
                        v-else-if="item.can_review && openReviewFor !== item.id"
                        type="button"
                        class="mt-1 text-xs text-indigo-600 hover:underline"
                        @click="startReview(item)"
                    >
                        Tulis Ulasan
                    </button>

                    <form
                        v-if="reviewForms[item.id] && openReviewFor === item.id"
                        @submit.prevent="submitReview(item)"
                        class="mt-2 space-y-2 rounded-md bg-gray-50 p-3"
                    >
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700">Rating</label>
                            <select
                                v-model.number="reviewForms[item.id].rating"
                                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option v-for="n in [5, 4, 3, 2, 1]" :key="n" :value="n">{{ n }} bintang</option>
                            </select>
                        </div>
                        <textarea
                            v-model="reviewForms[item.id].comment"
                            rows="2"
                            placeholder="Bagaimana produk ini menurut Anda? (opsional)"
                            class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <div class="flex gap-2">
                            <PrimaryButton :disabled="reviewForms[item.id].processing">Kirim Ulasan</PrimaryButton>
                            <button type="button" class="text-sm text-gray-500 hover:underline" @click="openReviewFor = null">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
                <div class="mt-3 space-y-1 text-sm text-gray-600">
                    <div class="flex justify-between"><span>Subtotal</span><span>{{ formatPrice(order.subtotal) }}</span></div>
                    <div v-if="order.discount > 0" class="flex justify-between text-green-600">
                        <span>Diskon</span><span>-{{ formatPrice(order.discount) }}</span>
                    </div>
                    <div v-if="order.tax > 0" class="flex justify-between">
                        <span>Pajak</span><span>{{ formatPrice(order.tax) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Ongkos Kirim ({{ order.shipping_zone_name }})</span>
                        <span>{{ formatPrice(order.shipping_cost) }}</span>
                    </div>
                    <div class="flex justify-between text-base font-semibold text-gray-900">
                        <span>Total</span><span>{{ formatPrice(order.total) }}</span>
                    </div>
                </div>
                <a
                    :href="route('order.invoice', { order: order.order_number, email })"
                    class="mt-4 inline-block text-sm text-indigo-600 hover:underline"
                >
                    Unduh Invoice (PDF)
                </a>
            </div>

            <div v-if="order.payment?.method === 'midtrans' && order.payment.status === 'pending'" class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-3 font-semibold text-gray-900">Selesaikan Pembayaran</h2>
                <PrimaryButton :disabled="!snapReady" @click="payNow">Bayar Sekarang</PrimaryButton>
            </div>

            <div
                v-else-if="order.payment?.method === 'manual_transfer' && order.payment.status === 'pending'"
                class="rounded-lg bg-white p-6 shadow"
            >
                <h2 class="mb-3 font-semibold text-gray-900">Transfer Bank Manual</h2>
                <p class="mb-4 text-sm text-gray-600">
                    Silakan transfer sejumlah <strong>{{ formatPrice(order.total) }}</strong> ke rekening toko, lalu
                    unggah bukti transfer di bawah ini.
                </p>

                <form @submit.prevent="submitProof" class="flex items-center gap-3">
                    <input type="file" accept="image/*" required @change="proofForm.proof = $event.target.files[0]" class="text-sm" />
                    <PrimaryButton :disabled="proofForm.processing">Unggah Bukti</PrimaryButton>
                </form>
                <InputError :message="proofForm.errors.proof" />
            </div>

            <div v-else-if="order.payment?.proof_url" class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-3 font-semibold text-gray-900">Bukti Pembayaran</h2>
                <img :src="order.payment.proof_url" class="max-w-xs rounded border" />
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-3 font-semibold text-gray-900">Riwayat Status</h2>
                <ol class="space-y-2 text-sm">
                    <li v-for="(h, i) in order.status_histories" :key="i" class="border-l-2 border-indigo-200 pl-3">
                        <span class="font-medium text-gray-900">{{ h.status_label }}</span>
                        <span class="text-gray-500"> — {{ new Date(h.created_at).toLocaleString('id-ID') }}</span>
                        <p v-if="h.note" class="text-gray-600">{{ h.note }}</p>
                    </li>
                </ol>
            </div>
        </div>
    </StorefrontLayout>
</template>
