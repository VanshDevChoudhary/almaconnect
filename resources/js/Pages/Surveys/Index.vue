<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    surveys: { type: Array, default: () => [] },
});

const readTime = (n) => Math.max(1, Math.ceil(n * 0.5));
</script>

<template>
    <Head title="Surveys" />

    <AuthenticatedLayout>
        <div class="py-10">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold text-gray-900">Surveys</h1>
                <p class="mt-1 text-sm text-gray-600">Share your thoughts and help us improve.</p>

                <div class="mt-8 space-y-4">
                    <div
                        v-for="s in surveys"
                        :key="s.id"
                        class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="font-semibold text-gray-900">{{ s.title }}</h2>
                                <p v-if="s.description" class="mt-1 text-sm text-gray-600">{{ s.description }}</p>
                                <p class="mt-2 text-xs text-gray-500">
                                    {{ s.question_count }} question{{ s.question_count === 1 ? '' : 's' }}
                                    · ~{{ readTime(s.question_count) }} min
                                </p>
                            </div>
                            <div class="shrink-0">
                                <span
                                    v-if="s.completed"
                                    class="inline-flex items-center gap-1 rounded-full bg-green-50 px-3 py-1.5 text-sm font-medium text-green-700"
                                >
                                    ✓ Completed
                                </span>
                                <Link
                                    v-else
                                    :href="route('surveys.show', s.id)"
                                    class="rounded-lg bg-maroon-600 px-4 py-2 text-sm font-semibold text-white hover:bg-maroon-700"
                                >
                                    Take survey →
                                </Link>
                            </div>
                        </div>
                    </div>
                    <div v-if="!surveys.length" class="py-16 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                        </svg>
                        <h3 class="mt-3 text-sm font-semibold text-gray-900">No surveys for you</h3>
                        <p class="mt-1 text-sm text-gray-500">You're all caught up.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
