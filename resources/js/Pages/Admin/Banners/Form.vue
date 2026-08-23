<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    banner: Object,
});

const isEdit = computed(() => !!props.banner);
const imagePreview = ref(props.banner?.image ? `/storage/${props.banner.image}` : null);

const form = useForm({
    title: props.banner?.title ?? '',
    image: null,
    link_url: props.banner?.link_url ?? '',
    sort_order: props.banner?.sort_order ?? 0,
    is_active: props.banner?.is_active ?? true,
});

const onImageChange = (event) => {
    const file = event.target.files[0];
    form.image = file ?? null;
    imagePreview.value = file ? URL.createObjectURL(file) : null;
};

const submit = () => {
    if (isEdit.value) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(route('admin.banners.update', props.banner.id));
    } else {
        form.post(route('admin.banners.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Ubah Banner' : 'Tambah Banner'" />

    <AdminLayout>
        <template #title>{{ isEdit ? 'Ubah Banner' : 'Tambah Banner' }}</template>

        <form @submit.prevent="submit" class="max-w-xl space-y-4 rounded-lg bg-white p-6 shadow">
            <div>
                <InputLabel for="title" value="Judul" />
                <TextInput id="title" v-model="form.title" required autofocus />
                <InputError :message="form.errors.title" />
            </div>

            <div>
                <InputLabel for="image" :value="isEdit ? 'Gambar (kosongkan jika tidak diubah)' : 'Gambar'" />
                <img v-if="imagePreview" :src="imagePreview" class="mb-2 h-24 w-48 rounded object-cover" />
                <input id="image" type="file" accept="image/*" @change="onImageChange" class="block text-sm" />
                <InputError :message="form.errors.image" />
            </div>

            <div>
                <InputLabel for="link_url" value="Tautan (opsional)" />
                <TextInput id="link_url" v-model="form.link_url" placeholder="https://..." />
                <InputError :message="form.errors.link_url" />
            </div>

            <div>
                <InputLabel for="sort_order" value="Urutan Tampil" />
                <TextInput id="sort_order" v-model="form.sort_order" type="number" min="0" />
                <InputError :message="form.errors.sort_order" />
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300" />
                Aktif
            </label>

            <div class="flex items-center gap-3 pt-2">
                <PrimaryButton :disabled="form.processing">Simpan</PrimaryButton>
                <Link :href="route('admin.banners.index')"><SecondaryButton type="button">Batal</SecondaryButton></Link>
            </div>
        </form>
    </AdminLayout>
</template>
