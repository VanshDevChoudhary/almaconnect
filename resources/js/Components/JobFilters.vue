<script setup>
import { JOB_TYPE_LABELS } from '@/lib/format';

const props = defineProps({
    selectedTypes: { type: Array, default: () => [] },
    location: { type: String, default: '' },
});

const emit = defineEmits(['toggle-type', 'update:location', 'clear']);

const types = Object.entries(JOB_TYPE_LABELS).map(([value, label]) => ({ value, label }));

const hasFilters = () => props.selectedTypes.length > 0 || props.location !== '';
</script>

<template>
    <div>
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900">Filters</h2>
            <button
                v-if="hasFilters()"
                type="button"
                class="text-xs font-medium text-indigo-600 hover:text-indigo-700"
                @click="emit('clear')"
            >
                Clear
            </button>
        </div>

        <div class="mt-4">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Type</h3>
            <div class="mt-2 space-y-2">
                <label
                    v-for="t in types"
                    :key="t.value"
                    class="flex items-center gap-2 text-sm text-gray-700"
                >
                    <input
                        type="checkbox"
                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        :checked="selectedTypes.includes(t.value)"
                        @change="emit('toggle-type', t.value)"
                    />
                    {{ t.label }}
                </label>
            </div>
        </div>

        <div class="mt-5 border-t border-gray-100 pt-4">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Location</h3>
            <input
                :value="location"
                type="text"
                placeholder="e.g. Bangalore, Remote"
                class="mt-2 block w-full rounded-md border-gray-300 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                @input="emit('update:location', $event.target.value)"
            />
        </div>
    </div>
</template>
