<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

defineProps({
    orders: Array,
});

const form = useForm({
    order_id: '',
    subject: '',
    message: '',
});

const submit = () => {
    form.post(route('customer.chat.store'));
};
</script>

<template>
    <Head title="Mulai Obrolan Baru" />

    <CustomerLayout>
        <h1 class="mb-6 text-lg font-semibold text-gray-900">Mulai Obrolan dengan CS</h1>

        <form @submit.prevent="submit" class="max-w-xl space-y-4 rounded-lg bg-white p-6 shadow">
            <div>
                <InputLabel for="order_id" value="Terkait Pesanan (opsional)" />
                <select
                    id="order_id"
                    v-model="form.order_id"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
                    <option value="">Pertanyaan Umum</option>
                    <option v-for="order in orders" :key="order.id" :value="order.id">{{ order.order_number }}</option>
                </select>
                <InputError :message="form.errors.order_id" />
            </div>

            <div>
                <InputLabel for="subject" value="Subjek" />
                <TextInput id="subject" v-model="form.subject" required autofocus placeholder="mis. Pertanyaan seputar produk" />
                <InputError :message="form.errors.subject" />
            </div>

            <div>
                <InputLabel for="message" value="Pesan" />
                <textarea
                    id="message"
                    v-model="form.message"
                    rows="4"
                    required
                    placeholder="Tulis pesan Anda..."
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                ></textarea>
                <InputError :message="form.errors.message" />
            </div>

            <div class="flex items-center gap-3 pt-2">
                <PrimaryButton :disabled="form.processing">Kirim</PrimaryButton>
                <Link :href="route('customer.chat.index')"><SecondaryButton type="button">Batal</SecondaryButton></Link>
            </div>
        </form>
    </CustomerLayout>
</template>
