<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';

// Props datang dari App\Support\SeoMeta::make() (lihat controller storefront
// terkait) supaya judul/deskripsi/gambar konsisten dengan yang sudah dirender
// server-side di resources/views/app.blade.php — komponen ini hanya
// memperbarui tag yang sama saat navigasi SPA (client-side), bukan sumber
// data pertama untuk crawler tanpa JS.
const props = defineProps({
    seo: { type: Object, required: true },
});

const page = usePage();
const resolvedImage = computed(() => props.seo.image ?? page.props.storeSettings.logoUrl ?? null);
</script>

<template>
    <Head :title="seo.title">
        <meta v-if="seo.description" head-key="description" name="description" :content="seo.description" />
        <meta head-key="og:type" property="og:type" content="website" />
        <meta head-key="og:title" property="og:title" :content="seo.title" />
        <meta v-if="seo.description" head-key="og:description" property="og:description" :content="seo.description" />
        <meta v-if="resolvedImage" head-key="og:image" property="og:image" :content="resolvedImage" />
        <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />
    </Head>
</template>
