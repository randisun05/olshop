<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-50 px-4 py-10">
        <Link href="/" class="mb-8 text-xl font-semibold text-gray-800">
            Toko Online
        </Link>

        <div class="w-full max-w-md rounded-lg bg-white p-8 shadow">
            <slot />
        </div>
    </div>
</template>
