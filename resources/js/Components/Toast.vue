<script setup>
import { useToast } from '@/Composables/useToast';

const { toasts, dismiss } = useToast();
</script>

<template>
    <div class="pointer-events-none fixed inset-x-0 top-4 z-50 flex flex-col items-center gap-2">
        <TransitionGroup
            enter-active-class="transition duration-250 ease-out"
            enter-from-class="-translate-y-3 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="-translate-y-3 opacity-0"
        >
            <div
                v-for="t in toasts"
                :key="t.id"
                class="pointer-events-auto flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium shadow-lg"
                :class="
                    t.variant === 'error'
                        ? 'bg-red-600 text-white'
                        : 'bg-gray-900 text-white'
                "
            >
                <span>{{ t.message }}</span>
                <button
                    type="button"
                    class="text-white/70 hover:text-white"
                    @click="dismiss(t.id)"
                >
                    &times;
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>
