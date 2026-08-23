<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <header class="border-b bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
                <Link href="/" class="text-lg font-semibold text-gray-900">Toko Online</Link>

                <nav class="flex items-center gap-4 text-sm">
                    <template v-if="page.props.auth.user">
                        <Link :href="route('dashboard')" class="text-gray-700 hover:text-indigo-600">
                            Dashboard
                        </Link>
                        <Link :href="route('logout')" method="post" as="button" class="text-gray-700 hover:text-indigo-600">
                            Keluar
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" class="text-gray-700 hover:text-indigo-600">Masuk</Link>
                        <Link :href="route('register')" class="text-gray-700 hover:text-indigo-600">Daftar</Link>
                    </template>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8">
            <slot />
        </main>
    </div>
</template>
