<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    attribute: Object,
});

const isEdit = computed(() => !!props.attribute);

const form = useForm({
    name: props.attribute?.name ?? '',
    values: props.attribute
        ? props.attribute.values.map((v) => ({ id: v.id, value: v.value }))
        : [{ id: null, value: '' }],
});

const addValue = () => {
    form.values.push({ id: null, value: '' });
};

const removeValue = (index) => {
    if (form.values.length > 1) {
        form.values.splice(index, 1);
    }
};

const submit = () => {
    if (isEdit.value) {
        form.put(route('admin.attributes.update', props.attribute.id));
    } else {
        form.transform((data) => ({ ...data, values: data.values.map((v) => v.value) })).post(
            route('admin.attributes.store')
        );
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Ubah Atribut' : 'Tambah Atribut'" />

    <AdminLayout>
        <template #title>{{ isEdit ? 'Ubah Atribut' : 'Tambah Atribut' }}</template>

        <form @submit.prevent="submit" class="max-w-xl space-y-4 rounded-lg bg-white p-6 shadow">
            <div>
                <InputLabel for="name" value="Nama Atribut" />
                <TextInput id="name" v-model="form.name" required autofocus placeholder="mis. Warna" />
                <InputError :message="form.errors.name" />
            </div>

            <div>
                <InputLabel value="Nilai" />
                <div class="space-y-2">
                    <div v-for="(value, index) in form.values" :key="index" class="flex items-center gap-2">
                        <TextInput v-model="value.value" placeholder="mis. Merah" />
                        <button
                            type="button"
                            class="text-sm text-red-600 hover:underline"
                            @click="removeValue(index)"
                            v-if="form.values.length > 1"
                        >
                            Hapus
                        </button>
                    </div>
                </div>
                <InputError :message="form.errors.values" />
                <button type="button" class="mt-2 text-sm text-indigo-600 hover:underline" @click="addValue">
                    + Tambah Nilai
                </button>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <PrimaryButton :disabled="form.processing">Simpan</PrimaryButton>
                <Link :href="route('admin.attributes.index')"><SecondaryButton type="button">Batal</SecondaryButton></Link>
            </div>
        </form>
    </AdminLayout>
</template>
