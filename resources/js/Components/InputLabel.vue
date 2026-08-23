<script setup>
defineProps({
    for: String,
    value: String,
});
</script>

<template>
    <label :for="for" class="mb-1 block text-sm font-medium text-gray-700">
        <slot>{{ value }}</slot>
    </label>
</template>
