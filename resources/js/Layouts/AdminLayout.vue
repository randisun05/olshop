<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const permissions = page.props.auth.user?.permissions ?? [];
const hasPermission = (name) => !name || permissions.includes(name);

const nav = [
    { label: 'Dashboard', route: 'admin.dashboard' },
    { label: 'Produk', route: 'admin.products.index', permission: 'products.manage' },
    { label: 'Kategori', route: 'admin.categories.index', permission: 'categories.manage' },
    { label: 'Brand', route: 'admin.brands.index', permission: 'brands.manage' },
    { label: 'Atribut', route: 'admin.attributes.index', permission: 'attributes.manage' },
].filter((item) => hasPermission(item.permission));
</script>

<template>
    <div class="flex min-h-screen bg-gray-100">
        <aside class="w-64 bg-gray-900 text-gray-200">
            <div class="px-6 py-5 text-lg font-semibold text-white">Admin</div>
            <nav class="mt-2 space-y-1 px-3">
                <Link
                    v-for="item in nav"
                    :key="item.route"
                    :href="route(item.route)"
                    class="block rounded px-3 py-2 text-sm hover:bg-gray-800"
                    :class="{ 'bg-gray-800 text-white': route().current(item.route.replace('.index', '').concat('.*')) || route().current(item.route) }"
                >
                    {{ item.label }}
                </Link>
            </nav>
        </aside>

        <div class="flex-1">
            <header class="flex items-center justify-between border-b bg-white px-6 py-4">
                <h1 class="text-base font-semibold text-gray-800"><slot name="title">Admin</slot></h1>
                <div class="flex items-center gap-4 text-sm text-gray-600">
                    <span>{{ page.props.auth.user?.name }}</span>
                    <Link :href="route('logout')" method="post" as="button" class="text-red-600 hover:underline">
                        Keluar
                    </Link>
                </div>
            </header>

            <main class="p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
