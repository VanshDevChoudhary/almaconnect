<script setup>
import { Head, Link } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({ stories: { type: Array, default: () => [] } });

const badge = {
    pending: 'bg-amber-50 text-amber-700',
    published: 'bg-green-50 text-green-700',
    rejected: 'bg-red-50 text-red-700',
};
</script>

<template>
    <Head title="My stories" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">My stories</h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-3">
                <div
                    v-for="s in stories"
                    :key="s.slug"
                    class="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-sm"
                >
                    <div>
                        <p class="font-medium text-gray-900">{{ s.headline }}</p>
                        <p class="text-xs text-gray-500">Submitted {{ dayjs(s.created_at).format('MMM D, YYYY') }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-medium capitalize" :class="badge[s.status]">
                            {{ s.status }}
                        </span>
                        <Link
                            v-if="s.status === 'published'"
                            :href="route('stories.show', s.slug)"
                            class="text-sm font-medium text-indigo-600 hover:text-indigo-700"
                        >
                            View
                        </Link>
                    </div>
                </div>
                <div v-if="!stories.length" class="py-16 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                    <h3 class="mt-3 text-sm font-semibold text-gray-900">You haven't submitted a story</h3>
                    <p class="mt-1 text-sm text-gray-500">Inspire others with your journey.</p>
                    <Link :href="route('stories.submit')" class="mt-4 inline-block rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Submit a story
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
