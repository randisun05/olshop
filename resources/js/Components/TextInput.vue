<script setup>
defineProps({
    modelValue: String,
    type: { type: String, default: 'text' },
    id: String,
    required: Boolean,
    autofocus: Boolean,
    autocomplete: String,
});

defineEmits(['update:modelValue']);
</script>

<template>
    <input
        :id="id"
        :type="type"
        :value="modelValue"
        :required="required"
        :autofocus="autofocus"
        :autocomplete="autocomplete"
        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        @input="$emit('update:modelValue', $event.target.value)"
    />
</template>
