<script setup>
import { reactive, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    zones: Array,
});

const form = useForm({
    name: '',
    cost: '',
    is_active: true,
});

const editingId = ref(null);
const editForms = reactive({});

const submitCreate = () => {
    form.post(route('admin.shipping-zones.store'), {
        onSuccess: () => form.reset(),
    });
};

const startEdit = (zone) => {
    editingId.value = zone.id;
    editForms[zone.id] = useForm({ name: zone.name, cost: zone.cost, is_active: zone.is_active });
};

const submitEdit = (zone) => {
    editForms[zone.id].put(route('admin.shipping-zones.update', zone.id), {
        onSuccess: () => (editingId.value = null),
    });
};

const destroy = (zone) => {
    if (confirm(`Hapus wilayah "${zone.name}"?`)) {
        router.delete(route('admin.shipping-zones.destroy', zone.id));
    }
};
</script>

<template>
    <Head title="Wilayah Pengiriman" />

    <AdminLayout>
        <template #title>Wilayah Pengiriman</template>

        <p class="mb-4 text-sm text-gray-600">
            Tentukan tarif ongkos kirim per wilayah. Pelanggan memilih salah satu wilayah ini saat checkout.
        </p>

        <form @submit.prevent="submitCreate" class="mb-6 flex flex-wrap items-end gap-3 rounded-lg bg-white p-4 shadow">
            <div>
                <InputLabel for="name" value="Nama Wilayah" />
                <TextInput id="name" v-model="form.name" required placeholder="mis. Dalam Kota" />
                <InputError :message="form.errors.name" />
            </div>
            <div>
                <InputLabel for="cost" value="Ongkos Kirim (Rp)" />
                <TextInput id="cost" v-model="form.cost" type="number" min="0" required />
                <InputError :message="form.errors.cost" />
            </div>
            <PrimaryButton :disabled="form.processing">+ Tambah</PrimaryButton>
        </form>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Ongkos Kirim</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="zone in zones" :key="zone.id">
                        <template v-if="editingId === zone.id">
                            <td class="px-4 py-3"><TextInput v-model="editForms[zone.id].name" /></td>
                            <td class="px-4 py-3"><TextInput v-model="editForms[zone.id].cost" type="number" min="0" /></td>
                            <td class="px-4 py-3">
                                <input type="checkbox" v-model="editForms[zone.id].is_active" class="rounded border-gray-300" />
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button class="mr-3 text-indigo-600 hover:underline" @click="submitEdit(zone)">Simpan</button>
                                <button class="text-gray-500 hover:underline" @click="editingId = null">Batal</button>
                            </td>
                        </template>
                        <template v-else>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ zone.name }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(zone.cost) }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="zone.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                                >
                                    {{ zone.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button class="mr-3 text-indigo-600 hover:underline" @click="startEdit(zone)">Ubah</button>
                                <DangerButton @click="destroy(zone)">Hapus</DangerButton>
                            </td>
                        </template>
                    </tr>
                    <tr v-if="zones.length === 0">
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada wilayah pengiriman.</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>
    </AdminLayout>
</template>
