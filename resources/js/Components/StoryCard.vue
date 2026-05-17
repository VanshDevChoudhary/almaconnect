<script setup>
import { Link } from '@inertiajs/vue3';
import UserAvatar from '@/Components/UserAvatar.vue';

defineProps({
    story: { type: Object, required: true },
    featured: { type: Boolean, default: false },
});

const CATEGORY_LABELS = {
    entrepreneurship: 'Entrepreneurship',
    research: 'Research',
    social_impact: 'Social Impact',
    career: 'Career',
    other: 'Other',
};
</script>

<template>
    <Link
        :href="route('stories.show', story.slug)"
        :class="featured ? 'sm:flex' : 'block'"
        class="group overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md"
    >
        <div
            :class="featured ? 'sm:w-1/2 aspect-video sm:aspect-auto' : 'aspect-video'"
            class="w-full bg-gradient-to-r from-indigo-500 via-indigo-600 to-purple-600"
            :style="story.cover_image ? { backgroundImage: `url(/storage/${story.cover_image})`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}"
        ></div>
        <div :class="featured ? 'sm:w-1/2' : ''" class="p-5">
            <span class="inline-block rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wide text-indigo-700">
                {{ CATEGORY_LABELS[story.category] || story.category }}
            </span>
            <h3 :class="featured ? 'text-2xl' : 'text-lg'" class="mt-3 font-bold leading-tight text-gray-900">
                {{ story.headline }}
            </h3>
            <div class="mt-4 flex items-center gap-2">
                <UserAvatar :user="{ id: 0, name: story.author.name, avatar: story.author.avatar }" size="sm" />
                <span class="text-sm text-gray-600">
                    {{ story.author.name }}
                    <span v-if="story.author.branch" class="text-gray-400">
                        · {{ story.author.branch }} {{ story.author.batch }}
                    </span>
                </span>
            </div>
            <p class="mt-2 text-xs text-gray-500">{{ story.read_time }} min read</p>
        </div>
    </Link>
</template>
