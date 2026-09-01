<script setup>
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import Seo from '@/Components/Seo.vue';

defineProps({
    page: Object,
    seo: Object,
});
</script>

<template>
    <Seo :seo="seo" />

    <StorefrontLayout>
        <div class="rounded-lg bg-white p-8 shadow">
            <h1 class="mb-4 text-2xl font-bold text-gray-900">{{ page.title }}</h1>
            <div class="prose max-w-none whitespace-pre-line text-gray-700">{{ page.content }}</div>
        </div>
    </StorefrontLayout>
</template>
