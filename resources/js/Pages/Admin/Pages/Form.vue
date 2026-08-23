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
    page: Object,
});

const isEdit = computed(() => !!props.page);

const form = useForm({
    title: props.page?.title ?? '',
    slug: props.page?.slug ?? '',
    content: props.page?.content ?? '',
    is_active: props.page?.is_active ?? true,
});

const onTitleInput = () => {
    if (!isEdit.value) {
        form.slug = slugify(form.title);
    }
};

const submit = () => {
    if (isEdit.value) {
        form.put(route('admin.pages.update', props.page.slug));
    } else {
        form.post(route('admin.pages.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Ubah Halaman' : 'Tambah Halaman'" />

    <AdminLayout>
        <template #title>{{ isEdit ? 'Ubah Halaman' : 'Tambah Halaman' }}</template>

        <form @submit.prevent="submit" class="max-w-2xl space-y-4 rounded-lg bg-white p-6 shadow">
            <div>
                <InputLabel for="title" value="Judul" />
                <TextInput id="title" v-model="form.title" required autofocus @input="onTitleInput" />
                <InputError :message="form.errors.title" />
            </div>

            <div>
                <InputLabel for="slug" value="Slug" />
                <TextInput id="slug" v-model="form.slug" required />
                <InputError :message="form.errors.slug" />
            </div>

            <div>
                <InputLabel for="content" value="Konten" />
                <textarea
                    id="content"
                    v-model="form.content"
                    rows="10"
                    required
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                ></textarea>
                <InputError :message="form.errors.content" />
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300" />
                Aktif
            </label>

            <div class="flex items-center gap-3 pt-2">
                <PrimaryButton :disabled="form.processing">Simpan</PrimaryButton>
                <Link :href="route('admin.pages.index')"><SecondaryButton type="button">Batal</SecondaryButton></Link>
            </div>
        </form>
    </AdminLayout>
</template>
