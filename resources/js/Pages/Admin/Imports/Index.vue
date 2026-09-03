<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const page = usePage();

const categoriesForm = useForm({ file: null });
const brandsForm = useForm({ file: null });
const productsForm = useForm({ file: null });

const sections = [
    {
        key: 'categories',
        title: 'Impor Kategori',
        description: 'Kolom: nama (wajib), kategori_induk (opsional, harus sudah ada), aktif (opsional, ya/tidak).',
        route: 'admin.imports.categories',
        templateType: 'categories',
        form: categoriesForm,
    },
    {
        key: 'brands',
        title: 'Impor Brand',
        description: 'Kolom: nama (wajib).',
        route: 'admin.imports.brands',
        templateType: 'brands',
        form: brandsForm,
    },
    {
        key: 'products',
        title: 'Impor Produk',
        description:
            'Kolom: nama, kategori, brand (opsional), deskripsi (opsional), sku (opsional), harga, stok, berat (opsional). ' +
            'Satu baris = satu produk dengan satu varian standar (tanpa pilihan ukuran/warna). ' +
            'Produk dengan banyak varian tetap perlu ditambahkan lewat form admin.',
        route: 'admin.imports.products',
        templateType: 'products',
        form: productsForm,
    },
];

const onFileChange = (section, event) => {
    section.form.file = event.target.files[0] ?? null;
};

const upload = (section) => {
    section.form.post(route(section.route), {
        preserveScroll: true,
        onSuccess: () => {
            section.form.reset();
        },
    });
};
</script>

<template>
    <Head title="Impor Data" />

    <AdminLayout>
        <template #title>Impor Data</template>

        <div
            v-if="page.props.flash.success || page.props.flash.info || page.props.flash.error"
            class="mb-6 space-y-2"
        >
            <div v-if="page.props.flash.success" class="rounded-md bg-green-50 p-4 text-sm text-green-700">
                {{ page.props.flash.success }}
            </div>
            <div v-if="page.props.flash.info" class="rounded-md bg-blue-50 p-4 text-sm text-blue-700">
                {{ page.props.flash.info }}
            </div>
            <div v-if="page.props.flash.error" class="whitespace-pre-line rounded-md bg-red-50 p-4 text-sm text-red-700">
                {{ page.props.flash.error }}
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div v-for="section in sections" :key="section.key" class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-2 text-sm font-semibold text-gray-800">{{ section.title }}</h2>
                <p class="mb-4 text-xs text-gray-500">{{ section.description }}</p>

                <a
                    :href="route('admin.imports.template', section.templateType)"
                    class="mb-4 inline-block text-xs font-medium text-indigo-600 hover:underline"
                >
                    Unduh contoh format Excel
                </a>

                <input
                    type="file"
                    accept=".xlsx,.xls,.csv"
                    class="mb-1 block w-full text-xs"
                    @change="onFileChange(section, $event)"
                />
                <p v-if="section.form.errors.file" class="mb-2 text-xs text-red-600">{{ section.form.errors.file }}</p>

                <PrimaryButton
                    class="mt-2"
                    :disabled="!section.form.file || section.form.processing"
                    @click="upload(section)"
                >
                    {{ section.form.processing ? 'Mengunggah...' : 'Impor' }}
                </PrimaryButton>
            </div>
        </div>
    </AdminLayout>
</template>
