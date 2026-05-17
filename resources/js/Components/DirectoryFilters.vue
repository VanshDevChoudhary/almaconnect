<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    availableFilters: { type: Object, default: () => ({}) },
    appliedFilters: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['toggle', 'clear']);

const sections = [
    { key: 'batch', label: 'Graduation year' },
    { key: 'branch', label: 'Branch' },
    { key: 'industry', label: 'Industry' },
    { key: 'city', label: 'City' },
];

const citySearch = ref('');

const appliedCount = computed(() =>
    Object.values(props.appliedFilters || {}).reduce(
        (n, arr) => n + (Array.isArray(arr) ? arr.length : 0),
        0,
    ),
);

function entries(key) {
    const dist = props.availableFilters?.[key] || {};
    let list = Object.entries(dist).map(([value, count]) => ({ value, count }));

    if (key === 'batch') {
        list.sort((a, b) => Number(b.value) - Number(a.value));
    } else {
        list.sort((a, b) => b.count - a.count);
    }

    if (key === 'city') {
        if (citySearch.value) {
            list = list.filter((e) =>
                e.value.toLowerCase().includes(citySearch.value.toLowerCase()),
            );
        } else {
            list = list.slice(0, 10);
        }
    }
    return list;
}

function isActive(key, value) {
    const arr = props.appliedFilters?.[key] || [];
    return arr.map(String).includes(String(value));
}
</script>

<template>
    <div>
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900">
                Filters
                <span v-if="appliedCount" class="text-gray-400">· {{ appliedCount }}</span>
            </h2>
            <button
                v-if="appliedCount"
                type="button"
                class="text-xs font-medium text-indigo-600 hover:text-indigo-700"
                @click="emit('clear')"
            >
                Clear all
            </button>
        </div>

        <div
            v-for="section in sections"
            :key="section.key"
            class="mt-5 border-t border-gray-100 pt-4 first:mt-4 first:border-0 first:pt-0"
        >
            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                {{ section.label }}
            </h3>

            <input
                v-if="section.key === 'city'"
                v-model="citySearch"
                type="text"
                placeholder="Search cities…"
                class="mt-2 block w-full rounded-md border-gray-300 py-1.5 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            />

            <div class="mt-2 flex flex-wrap gap-2">
                <button
                    v-for="e in entries(section.key)"
                    :key="e.value"
                    type="button"
                    :class="[
                        'rounded-full px-3 py-1 text-xs font-medium transition duration-150 active:scale-95',
                        isActive(section.key, e.value)
                            ? 'bg-indigo-600 text-white'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                    ]"
                    @click="emit('toggle', section.key, e.value)"
                >
                    {{ e.value }} ({{ e.count }})
                </button>
                <p
                    v-if="!entries(section.key).length"
                    class="text-xs text-gray-400"
                >
                    No options
                </p>
            </div>
        </div>
    </div>
</template>
