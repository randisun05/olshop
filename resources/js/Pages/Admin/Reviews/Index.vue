<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';

defineProps({
    reviews: Object,
});

const destroy = (review) => {
    if (confirm('Hapus ulasan ini?')) {
        router.delete(route('admin.reviews.destroy', review.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Ulasan" />

    <AdminLayout>
        <template #title>Ulasan Produk</template>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Produk</th>
                        <th class="px-4 py-3">Pengguna</th>
                        <th class="px-4 py-3">Rating</th>
                        <th class="px-4 py-3">Komentar</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="review in reviews.data" :key="review.id">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ review.product_name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ review.user_name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ review.rating }} / 5</td>
                        <td class="px-4 py-3 max-w-xs truncate text-gray-600">{{ review.comment }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ new Date(review.created_at).toLocaleDateString('id-ID') }}</td>
                        <td class="px-4 py-3 text-right">
                            <DangerButton @click="destroy(review)">Hapus</DangerButton>
                        </td>
                    </tr>
                    <tr v-if="reviews.data.length === 0">
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada ulasan.</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-1">
            <Link
                v-for="link in reviews.links"
                :key="link.label"
                :href="link.url ?? '#'"
                v-html="link.label"
                class="rounded px-3 py-1 text-sm"
                :class="[
                    link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50',
                    !link.url && 'pointer-events-none opacity-50',
                ]"
            />
        </div>
    </AdminLayout>
</template>
