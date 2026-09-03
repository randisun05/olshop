<script setup>
import { ref } from 'vue';

const visible = ref(false);

const hasChoice = () => {
    try {
        return localStorage.getItem('cookie_consent') !== null;
    } catch (e) {
        return true;
    }
};

if (!hasChoice()) {
    visible.value = true;
}

const setChoice = (value) => {
    try {
        localStorage.setItem('cookie_consent', value);
    } catch (e) {
        // localStorage tidak tersedia (mis. mode private ketat) — banner cukup ditutup tanpa disimpan.
    }
    visible.value = false;

    if (value === 'accepted' && window.__initAnalytics) {
        window.__initAnalytics();
    }
};
</script>

<template>
    <div
        v-if="visible"
        class="fixed inset-x-0 bottom-0 z-50 flex flex-col items-center justify-between gap-3 border-t bg-white p-4 shadow-lg sm:flex-row"
    >
        <p class="text-sm text-gray-600">
            Kami menggunakan cookie untuk analitik guna meningkatkan pengalaman belanja Anda.
        </p>
        <div class="flex shrink-0 gap-2">
            <button
                type="button"
                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                @click="setChoice('rejected')"
            >
                Tolak
            </button>
            <button
                type="button"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
                @click="setChoice('accepted')"
            >
                Terima
            </button>
        </div>
    </div>
</template>
