<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    max: {
        type: Number,
        default: 20,
    },
});

const emit = defineEmits(['update:modelValue']);

const draft = ref('');

const atMax = computed(() => props.modelValue.length >= props.max);

function addSkill() {
    const value = draft.value.trim();
    if (!value) return;
    if (value.length > 50) return;
    if (atMax.value) return;

    const exists = props.modelValue.some(
        (s) => s.toLowerCase() === value.toLowerCase(),
    );
    if (!exists) {
        emit('update:modelValue', [...props.modelValue, value]);
    }
    draft.value = '';
}

function removeAt(index) {
    const next = props.modelValue.slice();
    next.splice(index, 1);
    emit('update:modelValue', next);
}

function onBackspace() {
    if (draft.value === '' && props.modelValue.length) {
        removeAt(props.modelValue.length - 1);
    }
}
</script>

<template>
    <div>
        <div
            class="flex flex-wrap items-center gap-2 rounded-md border border-gray-300 p-2 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500"
        >
            <TransitionGroup
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="scale-0 opacity-0"
                enter-to-class="scale-100 opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="scale-100 opacity-100"
                leave-to-class="scale-0 opacity-0"
            >
                <span
                    v-for="(skill, i) in modelValue"
                    :key="skill"
                    class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-700"
                >
                    {{ skill }}
                    <button
                        type="button"
                        class="text-indigo-400 hover:text-indigo-700"
                        @click="removeAt(i)"
                        aria-label="Remove skill"
                    >
                        &times;
                    </button>
                </span>
            </TransitionGroup>

            <input
                v-model="draft"
                type="text"
                :disabled="atMax"
                :placeholder="
                    atMax
                        ? 'Maximum reached'
                        : modelValue.length
                          ? 'Add another…'
                          : 'Type a skill and press Enter'
                "
                class="min-w-[8rem] flex-1 border-0 p-1 text-sm focus:outline-none focus:ring-0 disabled:bg-transparent disabled:text-gray-400"
                @keydown.enter.prevent="addSkill"
                @keydown.delete="onBackspace"
            />
        </div>
        <p class="mt-1 text-xs text-gray-500">{{ modelValue.length }} / {{ max }}</p>
    </div>
</template>
