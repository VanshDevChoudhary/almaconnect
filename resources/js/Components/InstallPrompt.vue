<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const visible = ref(false);
let deferredPrompt = null;
let timer = null;

function isDismissed() {
    const ts = localStorage.getItem('installDismissed');
    if (!ts) return false;
    return Date.now() - Number(ts) < 7 * 24 * 60 * 60 * 1000;
}

function dismiss() {
    localStorage.setItem('installDismissed', String(Date.now()));
    visible.value = false;
}

async function install() {
    if (!deferredPrompt) return;
    deferredPrompt.prompt();
    await deferredPrompt.userChoice;
    deferredPrompt = null;
    visible.value = false;
}

function maybeShow() {
    if (isDismissed() || !deferredPrompt) return;
    const visits = Number(localStorage.getItem('installVisits') ?? '0');
    if (visits >= 2) {
        visible.value = true;
    } else {
        timer = setTimeout(() => { visible.value = true; }, 30_000);
    }
}

function handlePrompt(e) {
    e.preventDefault();
    deferredPrompt = e;
    const visits = Number(localStorage.getItem('installVisits') ?? '0') + 1;
    localStorage.setItem('installVisits', String(visits));
    maybeShow();
}

onMounted(() => window.addEventListener('beforeinstallprompt', handlePrompt));
onUnmounted(() => {
    window.removeEventListener('beforeinstallprompt', handlePrompt);
    clearTimeout(timer);
});
</script>

<template>
    <Transition
        enter-active-class="transition duration-350 ease-out"
        enter-from-class="translate-y-4 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-250 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-4 opacity-0"
    >
        <div
            v-if="visible"
            class="fixed inset-x-4 bottom-20 z-40 flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-lg sm:inset-x-auto sm:bottom-4 sm:right-4 sm:max-w-sm"
            role="complementary"
            aria-label="Install app prompt"
        >
            <p class="text-sm font-medium text-gray-900">
                Install AlmaConnect for faster access
            </p>
            <div class="flex shrink-0 gap-2">
                <button
                    type="button"
                    class="rounded-md px-3 py-1.5 text-xs font-medium text-gray-500 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-400"
                    @click="dismiss"
                >
                    Not now
                </button>
                <button
                    type="button"
                    class="rounded-md bg-maroon-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:ring-offset-1"
                    @click="install"
                >
                    Install
                </button>
            </div>
        </div>
    </Transition>
</template>
