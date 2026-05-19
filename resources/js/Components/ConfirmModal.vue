<script setup>
import { ref, watch, nextTick, onMounted, onUnmounted } from 'vue';
import { useConfirm } from '@/composables/useConfirm';

const { state, handleConfirm, handleCancel } = useConfirm();
const modalEl = ref(null);

function trapFocus(e) {
    if (!state.value || !modalEl.value) return;
    const focusable = Array.from(
        modalEl.value.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])',
        ),
    ).filter((el) => !el.disabled);
    if (!focusable.length) return;
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (e.key === 'Escape') {
        handleCancel();
        return;
    }
    if (e.key !== 'Tab') return;
    if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
    }
}

watch(state, async (val) => {
    if (!val) return;
    await nextTick();
    modalEl.value?.querySelector('button')?.focus();
});

onMounted(() => document.addEventListener('keydown', trapFocus));
onUnmounted(() => document.removeEventListener('keydown', trapFocus));
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="state"
            class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            :aria-labelledby="'confirm-title'"
            @click.self="handleCancel"
        >
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
            >
                <div
                    v-if="state"
                    ref="modalEl"
                    class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl"
                >
                    <h3 id="confirm-title" class="text-base font-semibold text-gray-900">
                        {{ state.title }}
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">{{ state.body }}</p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-400"
                            @click="handleCancel"
                        >
                            {{ state.cancelLabel }}
                        </button>
                        <button
                            type="button"
                            :class="[
                                'rounded-lg px-4 py-2 text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-offset-1',
                                state.variant === 'danger'
                                    ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500'
                                    : state.variant === 'success'
                                      ? 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500'
                                      : 'bg-maroon-600 hover:bg-maroon-700 focus:ring-maroon-500',
                            ]"
                            @click="handleConfirm"
                        >
                            {{ state.confirmLabel }}
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>
