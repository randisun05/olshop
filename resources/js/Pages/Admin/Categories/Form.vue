<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { slugify } from '@/utils/slugify';

const props = defineProps({
    category: Object,
    categories: Array,
});

const isEdit = computed(() => !!props.category);

const form = useForm({
    name: props.category?.name ?? '',
    slug: props.category?.slug ?? '',
    parent_id: props.category?.parent_id ?? '',
    is_active: props.category?.is_active ?? true,
});

const onNameInput = () => {
    if (!isEdit.value) {
        form.slug = slugify(form.name);
    }
};

const submit = () => {
    if (isEdit.value) {
        form.put(route('admin.categories.update', props.category.id));
    } else {
        form.post(route('admin.categories.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Ubah Kategori' : 'Tambah Kategori'" />

    <AdminLayout>
        <template #title>{{ isEdit ? 'Ubah Kategori' : 'Tambah Kategori' }}</template>

        <form @submit.prevent="submit" class="max-w-xl space-y-4 rounded-lg bg-white p-6 shadow">
            <div>
                <InputLabel for="name" value="Nama Kategori" />
                <TextInput id="name" v-model="form.name" required autofocus @input="onNameInput" />
                <InputError :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="slug" value="Slug" />
                <TextInput id="slug" v-model="form.slug" required />
                <InputError :message="form.errors.slug" />
            </div>

            <div>
                <InputLabel for="parent_id" value="Kategori Induk (opsional)" />
                <select
                    id="parent_id"
                    v-model="form.parent_id"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
                    <option value="">- Tidak ada -</option>
                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <InputError :message="form.errors.parent_id" />
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300" />
                Aktif (tampil di toko)
            </label>

            <div class="flex items-center gap-3 pt-2">
                <PrimaryButton :disabled="form.processing">Simpan</PrimaryButton>
                <Link :href="route('admin.categories.index')"><SecondaryButton type="button">Batal</SecondaryButton></Link>
            </div>
        </form>
    </AdminLayout>
</template>
