<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import PostCard from '@/Components/PostCard.vue';
import { prefersReducedMotion } from '@/composables/useAuthAnimation';

const props = defineProps({
    posts: { type: Object, required: true }, // Laravel paginator payload
});

const items = ref([...props.posts.data]);
const loadedPage = ref(props.posts.current_page);
const loading = ref(false);
const sentinel = ref(null);
const listEl = ref(null);
let observer = null;

const hasMore = () => loadedPage.value < props.posts.last_page;

// Merge incoming pages / reflect mutations (like, comment, delete) coming
// back from partial reloads.
watch(
    () => props.posts,
    (next) => {
        if (next.current_page === 1) {
            // Fresh first page (e.g. after a post/comment mutation) —
            // replace while keeping any extra pages already loaded.
            const extra = items.value.filter(
                (i) => !next.data.some((d) => d.id === i.id) && i.__page > 1,
            );
            items.value = [
                ...next.data.map((d) => ({ ...d, __page: 1 })),
                ...extra,
            ];
            loadedPage.value = Math.max(loadedPage.value, 1);
        } else if (next.current_page > loadedPage.value) {
            const existing = new Set(items.value.map((i) => i.id));
            const fresh = next.data
                .filter((d) => !existing.has(d.id))
                .map((d) => ({ ...d, __page: next.current_page }));
            items.value.push(...fresh);
            loadedPage.value = next.current_page;
            nextTick(() => animateNew(fresh.length));
        } else {
            // Same page refreshed — update in place by id.
            const byId = new Map(next.data.map((d) => [d.id, d]));
            items.value = items.value.map((i) =>
                byId.has(i.id) ? { ...byId.get(i.id), __page: i.__page } : i,
            );
        }
        loading.value = false;
    },
    { deep: true },
);

function loadMore() {
    if (loading.value || !hasMore()) return;
    loading.value = true;
    router.reload({
        only: ['posts'],
        data: { page: loadedPage.value + 1 },
        preserveScroll: true,
        preserveState: true,
    });
}

function animateNew(count) {
    if (!listEl.value || prefersReducedMotion() || count <= 0) return;
    const cards = listEl.value.querySelectorAll('[data-post]');
    const fresh = Array.from(cards).slice(-count);
    gsap.from(fresh, { opacity: 0, y: 16, duration: 0.3, stagger: 0.06, ease: 'power2.out' });
}

onMounted(() => {
    if (listEl.value && !prefersReducedMotion()) {
        gsap.from(listEl.value.querySelectorAll('[data-post]'), {
            opacity: 0,
            y: 16,
            duration: 0.3,
            stagger: 0.06,
            ease: 'power2.out',
        });
    }
    observer = new IntersectionObserver(
        (entries) => entries[0].isIntersecting && loadMore(),
        { rootMargin: '200px' },
    );
    if (sentinel.value) observer.observe(sentinel.value);
});

onUnmounted(() => observer?.disconnect());

// Mark initial items with their page for the merge logic.
items.value = items.value.map((i) => ({ ...i, __page: props.posts.current_page }));
</script>

<template>
    <div>
        <div ref="listEl" class="space-y-4">
            <div v-for="post in items" :key="post.id" data-post>
                <PostCard :post="post" />
            </div>
        </div>

        <div v-if="!items.length" class="rounded-xl border border-dashed border-gray-300 p-10 text-center text-sm text-gray-500">
            No posts yet. Be the first to share something.
        </div>

        <div ref="sentinel" class="h-px"></div>

        <p v-if="loading" class="mt-4 text-center text-sm text-gray-500">
            ⌛ Loading more…
        </p>
        <p
            v-else-if="items.length && !hasMore()"
            class="mt-6 text-center text-sm text-gray-400"
        >
            You've reached the end
        </p>
    </div>
</template>
