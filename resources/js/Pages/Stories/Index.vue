<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StoryCard from '@/Components/StoryCard.vue';
import { prefersReducedMotion } from '@/composables/useAuthAnimation';

const props = defineProps({
    featured: { type: Object, default: null },
    stories: { type: Array, default: () => [] },
    category: { type: String, default: 'all' },
    canSubmit: { type: Boolean, default: false },
});

const cats = [
    { k: 'all', l: 'All' },
    { k: 'entrepreneurship', l: 'Entrepreneurship' },
    { k: 'research', l: 'Research' },
    { k: 'social_impact', l: 'Social Impact' },
    { k: 'career', l: 'Career' },
    { k: 'other', l: 'Other' },
];

const grid = ref(null);

function setCategory(c) {
    router.get(route('stories.index'), c === 'all' ? {} : { category: c }, {
        preserveState: true, preserveScroll: true, replace: true,
    });
}

function animate() {
    if (!grid.value || prefersReducedMotion()) return;
    gsap.from(grid.value.querySelectorAll('[data-story]'), {
        opacity: 0, y: 18, duration: 0.35, stagger: 0.08, ease: 'power2.out',
    });
}

let stop;
onMounted(() => {
    animate();
    stop = router.on('finish', () => nextTick(animate));
});
onUnmounted(() => stop?.());
</script>

<template>
    <Head title="Stories" />

    <AuthenticatedLayout>
        <div class="py-10">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Stories that inspire</h1>
                        <p class="mt-1 text-sm text-gray-600">
                            Journeys, breakthroughs and second acts from our alumni.
                        </p>
                    </div>
                    <Link
                        v-if="canSubmit"
                        :href="route('stories.submit')"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                    >
                        Submit your story
                    </Link>
                </div>

                <div class="mt-6 flex flex-wrap gap-2">
                    <button
                        v-for="c in cats"
                        :key="c.k"
                        type="button"
                        :class="[
                            'rounded-full px-4 py-1.5 text-sm font-medium transition active:scale-95',
                            category === c.k ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                        ]"
                        @click="setCategory(c.k)"
                    >
                        {{ c.l }}
                    </button>
                </div>

                <div ref="grid" class="mt-8 space-y-6">
                    <StoryCard v-if="featured" :story="featured" featured data-story />

                    <div v-if="stories.length" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <StoryCard v-for="s in stories" :key="s.slug" :story="s" data-story />
                    </div>

                    <p v-if="!featured" class="py-16 text-center text-sm text-gray-500">
                        No stories in this category yet.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
