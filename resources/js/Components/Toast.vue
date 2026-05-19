<script setup>
import { useToast } from '@/Composables/useToast';

const { toasts, dismiss } = useToast();

const variantCls = {
    success: 'bg-emerald-600',
    error: 'bg-red-600',
    info: 'bg-gray-800',
    warning: 'bg-amber-500',
};
</script>

<template>
    <div class="pointer-events-none fixed bottom-4 right-4 z-[55] flex flex-col-reverse items-end gap-2">
        <TransitionGroup
            enter-active-class="transition duration-250 ease-out"
            enter-from-class="translate-x-4 opacity-0"
            enter-to-class="translate-x-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-x-0 opacity-100"
            leave-to-class="translate-x-4 opacity-0"
        >
            <div
                v-for="t in toasts.slice(-4)"
                :key="t.id"
                class="pointer-events-auto flex w-72 items-start gap-3 rounded-lg px-4 py-3 text-sm font-medium text-white shadow-lg"
                :class="variantCls[t.variant] ?? variantCls.info"
                role="alert"
                aria-live="polite"
            >
                <!-- success -->
                <svg v-if="t.variant === 'success'" class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <!-- error -->
                <svg v-else-if="t.variant === 'error'" class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <!-- warning -->
                <svg v-else-if="t.variant === 'warning'" class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <!-- info / default -->
                <svg v-else class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span class="flex-1 leading-snug">{{ t.message }}</span>
                <button
                    type="button"
                    class="text-white/70 hover:text-white focus:outline-none"
                    :aria-label="'Dismiss notification'"
                    @click="dismiss(t.id)"
                >
                    &times;
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>
