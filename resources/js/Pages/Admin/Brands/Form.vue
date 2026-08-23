<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { slugify } from '@/utils/slugify';

const props = defineProps({
    brand: Object,
});

const isEdit = computed(() => !!props.brand);
const logoPreview = ref(props.brand?.logo ? `/storage/${props.brand.logo}` : null);

const form = useForm({
    name: props.brand?.name ?? '',
    slug: props.brand?.slug ?? '',
    logo: null,
});

const onNameInput = () => {
    if (!isEdit.value) {
        form.slug = slugify(form.name);
    }
};

const onLogoChange = (event) => {
    const file = event.target.files[0];
    form.logo = file ?? null;
    logoPreview.value = file ? URL.createObjectURL(file) : null;
};

const submit = () => {
    if (isEdit.value) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(route('admin.brands.update', props.brand.id));
    } else {
        form.post(route('admin.brands.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Ubah Brand' : 'Tambah Brand'" />

    <AdminLayout>
        <template #title>{{ isEdit ? 'Ubah Brand' : 'Tambah Brand' }}</template>

        <form @submit.prevent="submit" class="max-w-xl space-y-4 rounded-lg bg-white p-6 shadow">
            <div>
                <InputLabel for="name" value="Nama Brand" />
                <TextInput id="name" v-model="form.name" required autofocus @input="onNameInput" />
                <InputError :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="slug" value="Slug" />
                <TextInput id="slug" v-model="form.slug" required />
                <InputError :message="form.errors.slug" />
            </div>

            <div>
                <InputLabel for="logo" value="Logo (opsional)" />
                <img v-if="logoPreview" :src="logoPreview" class="mb-2 h-16 w-16 rounded object-cover" />
                <input id="logo" type="file" accept="image/*" @change="onLogoChange" class="block text-sm" />
                <InputError :message="form.errors.logo" />
            </div>

            <div class="flex items-center gap-3 pt-2">
                <PrimaryButton :disabled="form.processing">Simpan</PrimaryButton>
                <Link :href="route('admin.brands.index')"><SecondaryButton type="button">Batal</SecondaryButton></Link>
            </div>
        </form>
    </AdminLayout>
</template>
