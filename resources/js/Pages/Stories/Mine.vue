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
                <p v-if="!stories.length" class="py-16 text-center text-sm text-gray-500">
                    You haven't submitted any stories yet.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
