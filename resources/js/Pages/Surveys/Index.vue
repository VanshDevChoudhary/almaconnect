<script setup>
import { ref, watch } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useToast } from '@/Composables/useToast';

const props = defineProps({
    surveys: { type: Array, default: () => [] },
});

const page = usePage();
const { showToast } = useToast();

watch(
    () => page.props.flash?.success,
    (v) => { if (v) showToast(v); },
    { immediate: true },
);

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
                                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                                >
                                    Take survey →
                                </Link>
                            </div>
                        </div>
                    </div>
                    <p v-if="!surveys.length" class="py-16 text-center text-sm text-gray-500">
                        No active surveys right now.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
