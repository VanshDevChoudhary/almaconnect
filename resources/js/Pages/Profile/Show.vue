<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UserAvatar from '@/Components/UserAvatar.vue';
import { prefersReducedMotion } from '@/composables/useAuthAnimation';

const props = defineProps({
    profile: { type: Object, required: true },
    profileUser: { type: Object, required: true },
    isOwner: { type: Boolean, default: false },
});

const root = ref(null);
const avatarEl = ref(null);

const isVerified = computed(() => props.profileUser.status === 'approved');
const hasLinks = computed(
    () => props.profile.linkedin_url || props.profile.website_url,
);

onMounted(() => {
    if (!root.value || prefersReducedMotion()) return;

    gsap.from(root.value, { opacity: 0, duration: 0.25, ease: 'power2.out' });
    if (avatarEl.value) {
        gsap.from(avatarEl.value, {
            scale: 0.9,
            opacity: 0,
            duration: 0.4,
            ease: 'back.out(1.6)',
        });
    }
    const chips = root.value.querySelectorAll('[data-skill-chip]');
    if (chips.length) {
        gsap.from(chips, {
            opacity: 0,
            y: 6,
            duration: 0.2,
            stagger: 0.04,
            ease: 'power2.out',
            delay: 0.15,
        });
    }
});
</script>

<template>
    <Head :title="profileUser.name" />

    <AuthenticatedLayout>
        <div ref="root" class="py-10">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
                >
                    <!-- Hero band -->
                    <div class="relative">
                        <div
                            class="hero-band h-48 bg-gradient-to-r from-maroon-500 via-maroon-600 to-maroon-600"
                        ></div>

                        <Link
                            v-if="isOwner"
                            :href="route('profile.edit')"
                            class="absolute right-4 top-4 inline-flex items-center gap-1.5 rounded-lg bg-white/90 px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm backdrop-blur transition hover:bg-white"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                            </svg>
                            Edit profile
                        </Link>

                        <div class="px-8 pb-6">
                            <div ref="avatarEl" class="-mt-16">
                                <UserAvatar :user="profileUser" size="xl" ring />
                            </div>
                            <div class="mt-4 flex items-center gap-2">
                                <h1 class="text-2xl font-bold text-gray-900">
                                    {{ profileUser.name }}
                                </h1>
                                <span
                                    v-if="isVerified"
                                    title="Verified by the alumni cell"
                                    class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-amber-500"
                                >
                                    <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                </span>
                            </div>
                            <p
                                v-if="profile.batch || profile.branch"
                                class="mt-1"
                            >
                                <span
                                    class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-700"
                                >
                                    <template v-if="profile.batch">Class of {{ profile.batch }}</template>
                                    <template v-if="profile.batch && profile.branch"> · </template>
                                    <template v-if="profile.branch">{{ profile.branch }}</template>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Info card -->
                <div
                    class="mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
                >
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                        Currently
                    </h2>
                    <p v-if="profile.current_role || profile.current_company" class="mt-2 text-lg text-gray-900">
                        <span v-if="profile.current_role">{{ profile.current_role }}</span>
                        <span v-if="profile.current_role && profile.current_company"> at </span>
                        <span v-if="profile.current_company" class="font-semibold">{{ profile.current_company }}</span>
                    </p>
                    <p v-else class="mt-2 text-sm text-gray-400">
                        {{ isOwner ? 'Add your current role and company from Edit profile.' : 'No role added yet.' }}
                    </p>

                    <div class="mt-3 space-y-1 text-sm text-gray-600">
                        <p v-if="profile.city || profile.country">
                            📍 {{ [profile.city, profile.country].filter(Boolean).join(', ') }}
                        </p>
                        <p v-if="profile.industry">🏢 {{ profile.industry }}</p>
                    </div>

                    <div v-if="hasLinks" class="mt-4 flex flex-wrap gap-4">
                        <a
                            v-if="profile.linkedin_url"
                            :href="profile.linkedin_url"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center gap-1.5 text-sm font-medium text-maroon-600 hover:text-maroon-700"
                        >
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M4.98 3.5C4.98 4.88 3.87 6 2.5 6S0 4.88 0 3.5 1.12 1 2.5 1 4.98 2.12 4.98 3.5zM.22 8h4.56v15H.22V8zm7.5 0h4.37v2.05h.06c.61-1.15 2.1-2.36 4.32-2.36 4.62 0 5.47 3.04 5.47 7v8.31h-4.56v-7.36c0-1.76-.03-4.02-2.45-4.02-2.45 0-2.83 1.91-2.83 3.89V23H7.72V8z"/></svg>
                            LinkedIn
                        </a>
                        <a
                            v-if="profile.website_url"
                            :href="profile.website_url"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center gap-1.5 text-sm font-medium text-maroon-600 hover:text-maroon-700"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" /></svg>
                            Website
                        </a>
                    </div>
                </div>

                <!-- About -->
                <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                        About
                    </h2>
                    <p
                        v-if="profile.bio"
                        class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-700"
                    >{{ profile.bio }}</p>
                    <p v-else class="mt-2 text-sm text-gray-400">
                        <template v-if="isOwner">
                            This user hasn't added a bio yet.
                            <Link :href="route('profile.edit')" class="font-medium text-maroon-600 hover:text-maroon-700">Add a bio</Link>
                        </template>
                        <template v-else>This user hasn't added a bio yet.</template>
                    </p>
                </div>

                <!-- Skills -->
                <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                        Skills
                    </h2>
                    <div v-if="profile.skills.length" class="mt-3 flex flex-wrap gap-2">
                        <span
                            v-for="skill in profile.skills"
                            :key="skill"
                            data-skill-chip
                            class="rounded-full bg-gray-100 px-3 py-1.5 text-sm text-gray-700"
                        >
                            {{ skill }}
                        </span>
                    </div>
                    <p v-else class="mt-2 text-sm text-gray-400">No skills added yet.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.hero-band {
    background-size: 200% 200%;
    animation: hero-shift 5s linear infinite;
}
@keyframes hero-shift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
@media (prefers-reduced-motion: reduce) {
    .hero-band { animation: none; }
}
</style>

