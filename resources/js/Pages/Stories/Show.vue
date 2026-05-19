<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import dayjs from 'dayjs';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StoryCard from '@/Components/StoryCard.vue';
import UserAvatar from '@/Components/UserAvatar.vue';
import { renderMarkdown } from '@/lib/markdown';
import { prefersReducedMotion } from '@/composables/useAuthAnimation';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({
    story: { type: Object, required: true },
    related: { type: Array, default: () => [] },
});

const CATEGORY_LABELS = {
    entrepreneurship: 'Entrepreneurship',
    research: 'Research',
    social_impact: 'Social Impact',
    career: 'Career',
    other: 'Other',
};

const bodyEl = ref(null);
const rendered = computed(() => renderMarkdown(props.story.body));
const ogDescription = computed(() =>
    String(props.story.body || '').replace(/[#*_`>\[\]]/g, '').slice(0, 200),
);
const coverUrl = computed(() =>
    props.story.cover_image ? `/storage/${props.story.cover_image}` : null,
);

let triggers = [];

onMounted(() => {
    if (!bodyEl.value || prefersReducedMotion()) return;
    const blocks = bodyEl.value.querySelectorAll(':scope > *');
    blocks.forEach((el) => {
        const tween = gsap.fromTo(
            el,
            { opacity: 0, y: 20 },
            {
                opacity: 1,
                y: 0,
                duration: 0.6,
                ease: 'power2.out',
                scrollTrigger: { trigger: el, start: 'top 85%', once: true },
            },
        );
        if (tween.scrollTrigger) triggers.push(tween.scrollTrigger);
    });
});

onBeforeUnmount(() => {
    triggers.forEach((t) => t.kill());
    triggers = [];
});
</script>

<template>
    <Head>
        <title>{{ story.headline }}</title>
        <meta property="og:title" :content="story.headline" />
        <meta property="og:description" :content="ogDescription" />
        <meta v-if="coverUrl" property="og:image" :content="coverUrl" />
        <meta property="og:type" content="article" />
        <meta name="twitter:card" content="summary_large_image" />
    </Head>

    <AuthenticatedLayout>
        <div class="py-10">
            <article class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <Link :href="route('stories.index')" class="text-sm text-gray-500 hover:text-gray-700">
                    ← All stories
                </Link>

                <span class="mt-6 inline-block rounded-full bg-maroon-50 px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wide text-maroon-700">
                    {{ CATEGORY_LABELS[story.category] || story.category }}
                </span>

                <h1 class="mt-3 text-4xl font-bold leading-tight text-gray-900">
                    {{ story.headline }}
                </h1>

                <div class="mt-5 flex items-center gap-3">
                    <UserAvatar :user="{ id: story.author.id, name: story.author.name, avatar: story.author.avatar }" size="md" />
                    <div class="text-sm">
                        <p class="font-medium text-gray-900">{{ story.author.name }}</p>
                        <p class="text-gray-500">
                            <span v-if="story.published_at">Published {{ dayjs(story.published_at).format('MMM D, YYYY') }} · </span>
                            {{ story.read_time }} min read
                        </p>
                    </div>
                </div>

                <div
                    v-if="coverUrl"
                    class="mt-8 aspect-video w-full overflow-hidden rounded-xl"
                >
                    <img :src="coverUrl" :alt="story.headline" class="h-full w-full object-cover" />
                </div>

                <div ref="bodyEl" class="story-body mt-10 space-y-6 text-lg leading-relaxed text-gray-800" v-html="rendered"></div>

                <hr class="my-12 border-gray-200" />

                <section>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">About the author</h2>
                    <div class="mt-4 flex items-center gap-4">
                        <UserAvatar :user="{ id: story.author.id, name: story.author.name, avatar: story.author.avatar }" size="lg" />
                        <div>
                            <p class="font-semibold text-gray-900">{{ story.author.name }}</p>
                            <p class="text-sm text-gray-600">
                                <span v-if="story.author.role">{{ story.author.role }}</span>
                                <span v-if="story.author.role && story.author.company"> at </span>
                                <span v-if="story.author.company">{{ story.author.company }}</span>
                            </p>
                            <p v-if="story.author.city" class="text-sm text-gray-500">{{ story.author.city }}</p>
                            <Link
                                v-if="story.author.slug"
                                :href="route('profile.show', story.author.slug)"
                                class="mt-1 inline-block text-sm font-medium text-maroon-600 hover:text-maroon-700"
                            >
                                View profile →
                            </Link>
                        </div>
                    </div>
                </section>

                <hr class="my-12 border-gray-200" />

                <section v-if="related.length">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">More stories</h2>
                    <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <StoryCard v-for="s in related" :key="s.slug" :story="s" />
                    </div>
                </section>
            </article>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.story-body :deep(> p:first-of-type) {
    font-size: 1.35rem;
    line-height: 1.85rem;
    color: #374151;
}
.story-body :deep(h2) { font-size: 1.5rem; font-weight: 700; color: #111827; }
.story-body :deep(h3) { font-size: 1.25rem; font-weight: 600; color: #111827; }
.story-body :deep(blockquote) {
    border-left: 3px solid #6366f1;
    padding-left: 1rem;
    font-style: italic;
    color: #4b5563;
}
.story-body :deep(ul) { list-style: disc; padding-left: 1.5rem; }
.story-body :deep(ol) { list-style: decimal; padding-left: 1.5rem; }
.story-body :deep(a) { color: #4f46e5; text-decoration: underline; }
</style>
