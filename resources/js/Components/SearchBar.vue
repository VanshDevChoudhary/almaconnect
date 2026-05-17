<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'search']);

const local = ref(props.modelValue);
let timer = null;

watch(
    () => props.modelValue,
    (v) => {
        if (v !== local.value) local.value = v;
    },
);

watch(local, (v) => {
    emit('update:modelValue', v);
    clearTimeout(timer);
    timer = setTimeout(() => emit('search', v), 300);
});

function clear() {
    local.value = '';
    clearTimeout(timer);
    emit('search', '');
}
</script>

<template>
    <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
        </span>
        <input
            v-model="local"
            type="search"
            placeholder="Search by name, company, skill…"
            class="block w-full rounded-lg border-gray-300 py-2.5 pl-10 pr-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        />
        <button
            v-if="local"
            type="button"
            class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-700"
            @click="clear"
            aria-label="Clear search"
        >
            &times;
        </button>
    </div>
</template>
