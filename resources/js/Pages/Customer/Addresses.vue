<script setup>
import { reactive, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    addresses: Array,
});

const editingId = ref(null);
const showForm = ref(false);

const emptyForm = () => ({
    label: 'Rumah',
    recipient_name: '',
    phone: '',
    city: '',
    postal_code: '',
    address_line: '',
    is_default: false,
});

const form = useForm(emptyForm());

const startCreate = () => {
    editingId.value = null;
    form.defaults(emptyForm());
    form.reset();
    showForm.value = true;
};

const startEdit = (address) => {
    editingId.value = address.id;
    form.defaults({ ...address });
    form.reset();
    showForm.value = true;
};

const submit = () => {
    if (editingId.value) {
        form.put(route('customer.addresses.update', editingId.value), {
            onSuccess: () => (showForm.value = false),
        });
    } else {
        form.post(route('customer.addresses.store'), {
            onSuccess: () => (showForm.value = false),
        });
    }
};

const destroy = (address) => {
    if (confirm(`Hapus alamat "${address.label}"?`)) {
        router.delete(route('customer.addresses.destroy', address.id));
    }
};
</script>

<template>
    <Head title="Alamat Saya" />

    <CustomerLayout>
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-lg font-semibold text-gray-900">Alamat Saya</h1>
            <SecondaryButton @click="startCreate">+ Tambah Alamat</SecondaryButton>
        </div>

        <form v-if="showForm" @submit.prevent="submit" class="mb-6 grid grid-cols-1 gap-4 rounded-lg bg-white p-6 shadow sm:grid-cols-2">
            <div>
                <InputLabel for="label" value="Label" />
                <TextInput id="label" v-model="form.label" required />
                <InputError :message="form.errors.label" />
            </div>
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
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" v-model="form.is_default" class="rounded border-gray-300" />
                Jadikan alamat utama
            </label>
            <div class="sm:col-span-2 flex gap-3">
                <PrimaryButton :disabled="form.processing">Simpan</PrimaryButton>
                <SecondaryButton type="button" @click="showForm = false">Batal</SecondaryButton>
            </div>
        </form>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div v-for="address in addresses" :key="address.id" class="rounded-lg bg-white p-4 shadow">
                <div class="flex items-center justify-between">
                    <span class="font-medium text-gray-900">{{ address.label }}</span>
                    <span v-if="address.is_default" class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs text-indigo-700">
                        Utama
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-600">
                    {{ address.recipient_name }} — {{ address.phone }}<br>
                    {{ address.address_line }}, {{ address.city }} {{ address.postal_code }}
                </p>
                <div class="mt-3 flex gap-3 text-sm">
                    <button type="button" class="text-indigo-600 hover:underline" @click="startEdit(address)">Ubah</button>
                    <button type="button" class="text-red-600 hover:underline" @click="destroy(address)">Hapus</button>
                </div>
            </div>
            <p v-if="addresses.length === 0" class="text-gray-500">Belum ada alamat tersimpan.</p>
        </div>
    </CustomerLayout>
</template>
