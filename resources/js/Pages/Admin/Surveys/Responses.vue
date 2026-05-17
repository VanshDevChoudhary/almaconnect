<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import dayjs from 'dayjs';

const props = defineProps({
    survey: { type: Object, required: true },
    respondents: { type: Number, default: 0 },
    questions: { type: Array, default: () => [] },
});

function barPct(count) {
    return props.respondents > 0 ? Math.round((count / props.respondents) * 100) : 0;
}
</script>

<template>
    <Head :title="`Responses — ${survey.title}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ survey.title }}</h2>
                <Link :href="route('admin.surveys.index')" class="text-sm text-gray-500 hover:text-gray-700">← Surveys</Link>
            </div>
        </template>
        <div class="py-10">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-8">
                <p class="text-sm text-gray-600 font-medium">{{ respondents }} respondent{{ respondents === 1 ? '' : 's' }}</p>

                <div v-for="(q, i) in questions" :key="q.id" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Q{{ i + 1 }}</p>
                    <p class="mt-1 font-medium text-gray-900">{{ q.question }}</p>

                    <div v-if="q.type === 'text'" class="mt-4 space-y-2">
                        <div v-for="a in q.answers" :key="a.date" class="rounded-lg bg-gray-50 p-3 text-sm">
                            <p class="text-gray-800">"{{ a.answer }}"</p>
                            <p class="mt-1 text-xs text-gray-500">— {{ a.user }} · {{ dayjs(a.date).format('MMM D') }}</p>
                        </div>
                        <p v-if="!q.answers?.length" class="text-sm text-gray-400">No responses yet.</p>
                    </div>

                    <div v-else class="mt-4 space-y-2">
                        <div v-for="row in q.distribution" :key="row.option" class="flex items-center gap-3 text-sm">
                            <span class="w-36 shrink-0 truncate text-gray-700">{{ row.option }}</span>
                            <div class="flex-1 overflow-hidden rounded-full bg-gray-100 h-3">
                                <div class="h-full rounded-full bg-indigo-600 transition-[width] duration-700" :style="{ width: barPct(row.count) + '%' }"></div>
                            </div>
                            <span class="w-16 shrink-0 text-right text-gray-600">{{ row.count }} ({{ barPct(row.count) }}%)</span>
                        </div>
                        <p v-if="!q.distribution?.length" class="text-sm text-gray-400">No responses yet.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
