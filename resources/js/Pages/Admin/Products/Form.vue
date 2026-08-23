<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { slugify } from '@/utils/slugify';

const props = defineProps({
    product: Object,
    categories: Array,
    brands: Array,
    attributes: Array,
});

const isEdit = computed(() => !!props.product);

function selectionsFromValueIds(valueIds) {
    const selections = {};
    for (const attribute of props.attributes) {
        const match = attribute.values.find((v) => valueIds.includes(v.id));
        selections[attribute.id] = match ? match.id : '';
    }
    return selections;
}

function emptySelections() {
    const selections = {};
    for (const attribute of props.attributes) {
        selections[attribute.id] = '';
    }
    return selections;
}

const variantRows = reactive(
    props.product
        ? props.product.variants.map((v) => ({
              id: v.id,
              sku: v.sku,
              price: v.price,
              stock: v.stock,
              selections: selectionsFromValueIds(v.attribute_value_ids),
          }))
        : [{ id: null, sku: '', price: '', stock: 0, selections: emptySelections() }]
);

const addVariantRow = () => {
    variantRows.push({ id: null, sku: '', price: '', stock: 0, selections: emptySelections() });
};

const removeVariantRow = (index) => {
    if (variantRows.length > 1) {
        variantRows.splice(index, 1);
    }
};

const existingImages = ref(props.product?.images ?? []);
const deletedImageIds = ref([]);
const newImages = ref([]);

const onImagesChange = (event) => {
    newImages.value = [...newImages.value, ...Array.from(event.target.files)];
    event.target.value = '';
};

const removeNewImage = (index) => {
    newImages.value.splice(index, 1);
};

const removeExistingImage = (image) => {
    deletedImageIds.value.push(image.id);
    existingImages.value = existingImages.value.filter((i) => i.id !== image.id);
};

const form = useForm({
    name: props.product?.name ?? '',
    slug: props.product?.slug ?? '',
    category_id: props.product?.category_id ?? '',
    brand_id: props.product?.brand_id ?? '',
    description: props.product?.description ?? '',
    weight: props.product?.weight ?? 0,
    is_active: props.product?.is_active ?? true,
    is_featured: props.product?.is_featured ?? false,
});

const onNameInput = () => {
    if (!isEdit.value) {
        form.slug = slugify(form.name);
    }
};

const buildVariantsPayload = () =>
    variantRows.map((row) => ({
        id: row.id,
        sku: row.sku,
        price: row.price,
        stock: row.stock,
        attribute_value_ids: Object.values(row.selections).filter(Boolean),
    }));

const submit = () => {
    const payload = {
        ...form.data(),
        images: newImages.value,
        delete_image_ids: deletedImageIds.value,
        variants: buildVariantsPayload(),
    };

    if (isEdit.value) {
        form.transform(() => ({ ...payload, _method: 'put' })).post(route('admin.products.update', props.product.id), {
            forceFormData: true,
        });
    } else {
        form.transform(() => payload).post(route('admin.products.store'), {
            forceFormData: true,
        });
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Ubah Produk' : 'Tambah Produk'" />

    <AdminLayout>
        <template #title>{{ isEdit ? 'Ubah Produk' : 'Tambah Produk' }}</template>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="space-y-4 rounded-lg bg-white p-6 shadow lg:col-span-2">
                    <h2 class="font-semibold text-gray-900">Informasi Produk</h2>

                    <div>
                        <InputLabel for="name" value="Nama Produk" />
                        <TextInput id="name" v-model="form.name" required autofocus @input="onNameInput" />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="slug" value="Slug" />
                        <TextInput id="slug" v-model="form.slug" required />
                        <InputError :message="form.errors.slug" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="category_id" value="Kategori" />
                            <select
                                id="category_id"
                                v-model="form.category_id"
                                required
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="" disabled>Pilih kategori</option>
                                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                            <InputError :message="form.errors.category_id" />
                        </div>
                        <div>
                            <InputLabel for="brand_id" value="Brand (opsional)" />
                            <select
                                id="brand_id"
                                v-model="form.brand_id"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="">- Tidak ada -</option>
                                <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                            </select>
                            <InputError :message="form.errors.brand_id" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="description" value="Deskripsi" />
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                        <InputError :message="form.errors.description" />
                    </div>

                    <div>
                        <InputLabel for="weight" value="Berat (gram)" />
                        <TextInput id="weight" v-model="form.weight" type="number" min="0" required />
                        <InputError :message="form.errors.weight" />
                    </div>

                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300" />
                            Aktif (tampil di toko)
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" v-model="form.is_featured" class="rounded border-gray-300" />
                            Produk unggulan
                        </label>
                    </div>
                </div>

                <div class="space-y-4 rounded-lg bg-white p-6 shadow">
                    <h2 class="font-semibold text-gray-900">Gambar Produk</h2>

                    <div class="grid grid-cols-3 gap-2">
                        <div v-for="image in existingImages" :key="image.id" class="group relative">
                            <img :src="image.url" class="aspect-square w-full rounded object-cover" />
                            <button
                                type="button"
                                class="absolute right-1 top-1 rounded-full bg-red-600 px-1.5 text-xs text-white opacity-0 group-hover:opacity-100"
                                @click="removeExistingImage(image)"
                            >
                                ×
                            </button>
                        </div>
                        <div v-for="(file, index) in newImages" :key="index" class="group relative">
                            <img :src="URL.createObjectURL(file)" class="aspect-square w-full rounded object-cover" />
                            <button
                                type="button"
                                class="absolute right-1 top-1 rounded-full bg-red-600 px-1.5 text-xs text-white opacity-0 group-hover:opacity-100"
                                @click="removeNewImage(index)"
                            >
                                ×
                            </button>
                        </div>
                    </div>

                    <input type="file" accept="image/*" multiple @change="onImagesChange" class="block text-sm" />
                    <InputError :message="form.errors.images" />
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Varian &amp; Harga</h2>
                    <button type="button" class="text-sm text-indigo-600 hover:underline" @click="addVariantRow">
                        + Tambah Varian
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="text-left text-xs font-medium uppercase text-gray-500">
                            <tr>
                                <th v-for="attribute in attributes" :key="attribute.id" class="px-2 py-2">
                                    {{ attribute.name }}
                                </th>
                                <th class="px-2 py-2">SKU</th>
                                <th class="px-2 py-2">Harga</th>
                                <th class="px-2 py-2">Stok</th>
                                <th class="px-2 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(row, index) in variantRows" :key="index">
                                <td v-for="attribute in attributes" :key="attribute.id" class="px-2 py-2">
                                    <select
                                        v-model="row.selections[attribute.id]"
                                        class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">-</option>
                                        <option v-for="value in attribute.values" :key="value.id" :value="value.id">
                                            {{ value.value }}
                                        </option>
                                    </select>
                                </td>
                                <td class="px-2 py-2">
                                    <TextInput v-model="row.sku" placeholder="Otomatis jika kosong" />
                                </td>
                                <td class="px-2 py-2">
                                    <TextInput v-model="row.price" type="number" min="0" required />
                                </td>
                                <td class="px-2 py-2">
                                    <TextInput v-model="row.stock" type="number" min="0" required />
                                </td>
                                <td class="px-2 py-2">
                                    <button
                                        type="button"
                                        class="text-sm text-red-600 hover:underline"
                                        @click="removeVariantRow(index)"
                                        v-if="variantRows.length > 1"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <InputError :message="form.errors.variants" />
                <p class="mt-2 text-xs text-gray-500">
                    Untuk produk tanpa varian, cukup satu baris tanpa memilih atribut apa pun.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <PrimaryButton :disabled="form.processing">Simpan</PrimaryButton>
                <Link :href="route('admin.products.index')"><SecondaryButton type="button">Batal</SecondaryButton></Link>
            </div>
        </form>
    </AdminLayout>
</template>
