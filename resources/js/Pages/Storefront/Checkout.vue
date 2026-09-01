<script setup>
import { computed, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    items: Array,
    subtotal: Number,
    taxPercent: Number,
    shippingZones: Array,
    addresses: Array,
});

const page = usePage();
const isLoggedIn = computed(() => !!page.props.auth.user);

const useSavedAddress = ref(props.addresses.length > 0);

const form = useForm({
    use_saved_address: useSavedAddress.value,
    address_id: props.addresses.find((a) => a.is_default)?.id ?? props.addresses[0]?.id ?? '',
    recipient_name: '',
    phone: '',
    city: '',
    postal_code: '',
    address_line: '',
    guest_name: '',
    guest_email: '',
    guest_phone: '',
    shipping_zone_id: props.shippingZones[0]?.id ?? '',
    payment_method: 'manual_transfer',
    notes: '',
    coupon_code: '',
});

const selectedZone = computed(() => props.shippingZones.find((z) => z.id === form.shipping_zone_id));

const couponDiscount = ref(0);
const couponMessage = ref('');
const couponChecking = ref(false);
const couponApplied = ref(false);

const taxAmount = computed(() => Math.round((props.subtotal - couponDiscount.value) * (props.taxPercent / 100)));
const total = computed(() => props.subtotal - couponDiscount.value + taxAmount.value + Number(selectedZone.value?.cost ?? 0));

const formatPrice = (value) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);

const checkCoupon = async () => {
    if (!form.coupon_code) return;

    couponChecking.value = true;
    couponMessage.value = '';

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const response = await fetch(route('checkout.coupon.check'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ code: form.coupon_code }),
        });
        const data = await response.json();

        couponMessage.value = data.message;
        couponApplied.value = data.valid;
        couponDiscount.value = data.valid ? data.discount : 0;
    } finally {
        couponChecking.value = false;
    }
};

const submit = () => {
    form.use_saved_address = useSavedAddress.value;
    if (!couponApplied.value) {
        form.coupon_code = '';
    }
    form.post(route('checkout.store'));
};
</script>

<template>
    <Head title="Checkout" />

    <StorefrontLayout>
        <h1 class="mb-6 text-xl font-semibold text-gray-900">Checkout</h1>

        <form @submit.prevent="submit" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div v-if="!isLoggedIn" class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 font-semibold text-gray-900">Data Pemesan (Tanpa Akun)</h2>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <InputLabel for="guest_name" value="Nama" />
                            <TextInput id="guest_name" v-model="form.guest_name" required />
                            <InputError :message="form.errors.guest_name" />
                        </div>
                        <div>
                            <InputLabel for="guest_email" value="Email" />
                            <TextInput id="guest_email" v-model="form.guest_email" type="email" required />
                            <InputError :message="form.errors.guest_email" />
                        </div>
                        <div>
                            <InputLabel for="guest_phone" value="No. HP" />
                            <TextInput id="guest_phone" v-model="form.guest_phone" required />
                            <InputError :message="form.errors.guest_phone" />
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">
                        Simpan email ini untuk melacak pesanan Anda nanti melalui menu "Lacak Pesanan".
                    </p>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 font-semibold text-gray-900">Alamat Pengiriman</h2>

                    <div v-if="isLoggedIn && addresses.length > 0" class="mb-4 flex gap-4 text-sm">
                        <label class="flex items-center gap-2">
                            <input type="radio" :value="true" v-model="useSavedAddress" />
                            Gunakan alamat tersimpan
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" :value="false" v-model="useSavedAddress" />
                            Alamat baru
                        </label>
                    </div>

                    <div v-if="isLoggedIn && useSavedAddress && addresses.length > 0">
                        <select
                            v-model="form.address_id"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option v-for="address in addresses" :key="address.id" :value="address.id">
                                {{ address.label }} — {{ address.recipient_name }}, {{ address.city }}
                            </option>
                        </select>
                        <InputError :message="form.errors.address_id" />
                    </div>

                    <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="recipient_name" value="Nama Penerima" />
                            <TextInput id="recipient_name" v-model="form.recipient_name" required />
                            <InputError :message="form.errors.recipient_name" />
                        </div>
                        <div>
                            <InputLabel for="phone" value="No. HP" />
                            <TextInput id="phone" v-model="form.phone" required />
                            <InputError :message="form.errors.phone" />
                        </div>
                        <div>
                            <InputLabel for="city" value="Kota/Kabupaten" />
                            <TextInput id="city" v-model="form.city" required />
                            <InputError :message="form.errors.city" />
                        </div>
                        <div>
                            <InputLabel for="postal_code" value="Kode Pos (opsional)" />
                            <TextInput id="postal_code" v-model="form.postal_code" />
                        </div>
                        <div class="sm:col-span-2">
                            <InputLabel for="address_line" value="Alamat Lengkap" />
                            <textarea
                                id="address_line"
                                v-model="form.address_line"
                                rows="3"
                                required
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            />
                            <InputError :message="form.errors.address_line" />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 font-semibold text-gray-900">Pengiriman</h2>
                    <select
                        v-model="form.shipping_zone_id"
                        required
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                        <option v-for="zone in shippingZones" :key="zone.id" :value="zone.id">
                            {{ zone.name }} — {{ formatPrice(zone.cost) }}
                        </option>
                    </select>
                    <InputError :message="form.errors.shipping_zone_id" />
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 font-semibold text-gray-900">Metode Pembayaran</h2>
                    <div class="space-y-2 text-sm">
                        <label class="flex items-center gap-2">
                            <input type="radio" value="manual_transfer" v-model="form.payment_method" />
                            Transfer Bank Manual (unggah bukti setelah checkout)
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" value="midtrans" v-model="form.payment_method" />
                            Pembayaran Online (Kartu/VA/E-Wallet/QRIS via Midtrans)
                        </label>
                    </div>
                    <InputError :message="form.errors.payment_method" />
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <InputLabel for="coupon_code" value="Kode Kupon (opsional)" />
                    <div class="flex gap-2">
                        <TextInput id="coupon_code" v-model="form.coupon_code" placeholder="mis. HEMAT10" @input="couponApplied = false" />
                        <button
                            type="button"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                            :disabled="couponChecking"
                            @click="checkCoupon"
                        >
                            Terapkan
                        </button>
                    </div>
                    <p v-if="couponMessage" class="mt-1 text-sm" :class="couponApplied ? 'text-green-600' : 'text-red-600'">
                        {{ couponMessage }}
                    </p>
                    <InputError :message="form.errors.coupon_code" />
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <InputLabel for="notes" value="Catatan (opsional)" />
                    <textarea
                        id="notes"
                        v-model="form.notes"
                        rows="2"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                </div>
            </div>

            <div class="h-fit space-y-4 rounded-lg bg-white p-6 shadow">
                <h2 class="font-semibold text-gray-900">Ringkasan Pesanan</h2>
                <div v-for="item in items" :key="item.id" class="flex justify-between text-sm text-gray-600">
                    <span>{{ item.product_name }} ({{ item.variant_label }}) x{{ item.quantity }}</span>
                    <span>{{ formatPrice(item.subtotal) }}</span>
                </div>
                <div class="border-t pt-3 text-sm text-gray-600">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span>{{ formatPrice(subtotal) }}</span>
                    </div>
                    <div v-if="couponApplied" class="flex justify-between text-green-600">
                        <span>Diskon Kupon</span>
                        <span>-{{ formatPrice(couponDiscount) }}</span>
                    </div>
                    <div v-if="taxPercent > 0" class="flex justify-between">
                        <span>Pajak ({{ taxPercent }}%)</span>
                        <span>{{ formatPrice(taxAmount) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Ongkos Kirim</span>
                        <span>{{ formatPrice(selectedZone?.cost ?? 0) }}</span>
                    </div>
                    <div class="mt-2 flex justify-between text-base font-semibold text-gray-900">
                        <span>Total</span>
                        <span>{{ formatPrice(total) }}</span>
                    </div>
                </div>
                <PrimaryButton class="w-full" :disabled="form.processing">Buat Pesanan</PrimaryButton>
            </div>
        </form>
    </StorefrontLayout>
</template>
