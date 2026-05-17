<script setup>
import { ref, onMounted } from 'vue';
import { gsap } from 'gsap';
import { useToast } from '@/Composables/useToast';
import { prefersReducedMotion } from '@/composables/useAuthAnimation';

const props = defineProps({
    postId: { type: Number, required: true },
    liked: { type: Boolean, default: false },
    count: { type: Number, default: 0 },
});

const { showToast } = useToast();

const localLiked = ref(props.liked);
const localCount = ref(props.count);
const inFlight = ref(false);
const heart = ref(null);

async function toggle() {
    if (inFlight.value) return; // debounce rapid double-clicks
    inFlight.value = true;

    const prevLiked = localLiked.value;
    const prevCount = localCount.value;

    // Optimistic update
    localLiked.value = !prevLiked;
    localCount.value = prevCount + (localLiked.value ? 1 : -1);

    if (localLiked.value && heart.value && !prefersReducedMotion()) {
        gsap.fromTo(
            heart.value,
            { scale: 0.8 },
            { scale: 1, duration: 0.35, ease: 'back.out(3)', keyframes: [{ scale: 1.3 }, { scale: 1 }] },
        );
    }

    try {
        const res = await window.axios.post(route('posts.like', props.postId));
        // Server is authoritative — reconcile.
        localLiked.value = res.data.liked;
        localCount.value = res.data.likes_count;
    } catch (e) {
        localLiked.value = prevLiked;
        localCount.value = prevCount;
        showToast('Could not update your reaction.', 'error');
    } finally {
        inFlight.value = false;
    }
}
</script>

<template>
    <button
        type="button"
        class="inline-flex items-center gap-1.5 text-sm transition"
        :class="localLiked ? 'text-red-600' : 'text-gray-500 hover:text-gray-700'"
        @click="toggle"
    >
        <svg
            ref="heart"
            class="h-5 w-5"
            :fill="localLiked ? 'currentColor' : 'none'"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
        </svg>
        <span>{{ localCount }}</span>
    </button>
</template>
