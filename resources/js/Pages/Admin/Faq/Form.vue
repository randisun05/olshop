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
    entry: Object,
});

const isEdit = computed(() => !!props.entry);

const form = useForm({
    question: props.entry?.question ?? '',
    answer: props.entry?.answer ?? '',
    keywords: props.entry?.keywords ?? '',
    category: props.entry?.category ?? '',
    sort_order: props.entry?.sort_order ?? 0,
    is_active: props.entry?.is_active ?? true,
});

const submit = () => {
    if (isEdit.value) {
        form.put(route('admin.faq.update', props.entry.id));
    } else {
        form.post(route('admin.faq.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Ubah Entri FAQ' : 'Tambah Entri FAQ'" />

    <AdminLayout>
        <template #title>{{ isEdit ? 'Ubah Entri FAQ' : 'Tambah Entri FAQ' }}</template>

        <form @submit.prevent="submit" class="max-w-2xl space-y-4 rounded-lg bg-white p-6 shadow">
            <div>
                <InputLabel for="question" value="Pertanyaan" />
                <TextInput id="question" v-model="form.question" required autofocus />
                <InputError :message="form.errors.question" />
            </div>

            <div>
                <InputLabel for="answer" value="Jawaban (dipakai bot & halaman FAQ)" />
                <textarea
                    id="answer"
                    v-model="form.answer"
                    rows="4"
                    required
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                ></textarea>
                <InputError :message="form.errors.answer" />
            </div>

            <div>
                <InputLabel for="keywords" value="Kata Kunci (pisahkan dengan koma)" />
                <TextInput id="keywords" v-model="form.keywords" required placeholder="mis. resi, lacak, kirim, dimana pesanan" />
                <InputError :message="form.errors.keywords" />
                <p class="mt-1 text-xs text-gray-500">
                    Bot akan membalas otomatis dengan jawaban ini kalau pesan pelanggan mengandung salah satu kata kunci ini.
                </p>
            </div>

            <div>
                <InputLabel for="category" value="Kategori (opsional)" />
                <TextInput id="category" v-model="form.category" placeholder="mis. Order, Pengiriman, Pembayaran" />
                <InputError :message="form.errors.category" />
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
                <Link :href="route('admin.faq.index')"><SecondaryButton type="button">Batal</SecondaryButton></Link>
            </div>
        </form>
    </AdminLayout>
</template>
