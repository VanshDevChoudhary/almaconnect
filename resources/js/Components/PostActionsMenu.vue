<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useToast } from '@/Composables/useToast';

const props = defineProps({
    post: { type: Object, required: true },
});

const emit = defineEmits(['pin', 'unpin', 'delete']);

const { showToast } = useToast();
const open = ref(false);
const root = ref(null);

function onClickOutside(e) {
    if (root.value && !root.value.contains(e.target)) open.value = false;
}
onMounted(() => document.addEventListener('click', onClickOutside));
onUnmounted(() => document.removeEventListener('click', onClickOutside));

function copyLink() {
    const url = `${window.location.origin}${window.location.pathname}#post-${props.post.id}`;
    navigator.clipboard?.writeText(url);
    showToast('Link copied.');
    open.value = false;
}
</script>

<template>
    <div ref="root" class="relative">
        <button
            type="button"
            class="rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700"
            aria-label="Post actions"
            @click="open = !open"
        >
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm0 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm0 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" />
            </svg>
        </button>

        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
        >
            <div
                v-if="open"
                class="absolute right-0 z-20 mt-1 w-44 overflow-hidden rounded-lg border border-gray-200 bg-white py-1 shadow-lg"
            >
                <button
                    v-if="post.can_pin && !post.is_pinned"
                    type="button"
                    class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50"
                    @click="emit('pin'); open = false"
                >
                    📌 Pin post
                </button>
                <button
                    v-if="post.can_pin && post.is_pinned"
                    type="button"
                    class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50"
                    @click="emit('unpin'); open = false"
                >
                    Unpin post
                </button>
                <button
                    type="button"
                    class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50"
                    @click="copyLink"
                >
                    Copy link
                </button>
                <button
                    v-if="post.can_delete"
                    type="button"
                    class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50"
                    @click="emit('delete'); open = false"
                >
                    Delete
                </button>
            </div>
        </Transition>
    </div>
</template>
