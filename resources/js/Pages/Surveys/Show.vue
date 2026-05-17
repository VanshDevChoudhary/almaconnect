<script setup>
import { ref, reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    survey: { type: Object, required: true },
});

const answers = reactive({});
props.survey.questions.forEach((q) => {
    answers[q.id] = q.type === 'multi_choice' ? [] : '';
});

const processing = ref(false);
const errors = ref({});

function submit() {
    if (processing.value) return;
    processing.value = true;
    errors.value = {};

    router.post(
        route('surveys.respond', props.survey.id),
        { answers },
        {
            preserveScroll: true,
            onError: (errs) => {
                errors.value = errs;
                processing.value = false;
            },
            onFinish: () => (processing.value = false),
        },
    );
}
</script>

<template>
    <Head :title="survey.title" />

    <AuthenticatedLayout>
        <div class="py-10">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold text-gray-900">{{ survey.title }}</h1>
                <p v-if="survey.description" class="mt-2 text-sm text-gray-600">{{ survey.description }}</p>

                <form class="mt-8 space-y-8" @submit.prevent="submit">
                    <div
                        v-for="(q, idx) in survey.questions"
                        :key="q.id"
                        class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Question {{ idx + 1 }} of {{ survey.questions.length }}
                        </p>
                        <p class="mt-2 font-medium text-gray-900">{{ q.question }}</p>

                        <div class="mt-4">
                            <textarea
                                v-if="q.type === 'text'"
                                v-model="answers[q.id]"
                                rows="4"
                                maxlength="1000"
                                placeholder="Your answer…"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            ></textarea>

                            <div v-else-if="q.type === 'single_choice'" class="space-y-2">
                                <label
                                    v-for="opt in q.options"
                                    :key="opt"
                                    class="flex items-center gap-2 text-sm text-gray-700"
                                >
                                    <input
                                        type="radio"
                                        :value="opt"
                                        v-model="answers[q.id]"
                                        class="border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    {{ opt }}
                                </label>
                            </div>

                            <div v-else class="space-y-2">
                                <p class="text-xs text-gray-500">(Select all that apply)</p>
                                <label
                                    v-for="opt in q.options"
                                    :key="opt"
                                    class="flex items-center gap-2 text-sm text-gray-700"
                                >
                                    <input
                                        type="checkbox"
                                        :value="opt"
                                        v-model="answers[q.id]"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    {{ opt }}
                                </label>
                            </div>
                        </div>

                        <InputError class="mt-2" :message="errors[`answers.${q.id}`]" />
                    </div>

                    <button
                        type="submit"
                        :disabled="processing"
                        class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60"
                    >
                        {{ processing ? 'Submitting…' : 'Submit survey' }}
                    </button>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
