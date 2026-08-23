<script setup>
import { Head } from '@inertiajs/vue3';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';

defineProps({
    page: Object,
});
</script>

<template>
    <Head :title="page.title" />

    <StorefrontLayout>
        <div class="rounded-lg bg-white p-8 shadow">
            <h1 class="mb-4 text-2xl font-bold text-gray-900">{{ page.title }}</h1>
            <div class="prose max-w-none whitespace-pre-line text-gray-700">{{ page.content }}</div>
        </div>
    </StorefrontLayout>
</template>
