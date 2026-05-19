<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    mode: { type: String, default: 'create' },
    survey: { type: Object, default: () => ({}) },
});

const form = reactive({
    title: props.survey.title ?? '',
    description: props.survey.description ?? '',
    target_audience: props.survey.target_audience ?? 'all',
    is_active: props.survey.is_active ?? true,
    questions: (props.survey.questions ?? []).map((q) => ({
        question: q.question,
        type: q.type,
        options: q.options?.length ? [...q.options, ''] : ['', ''],
    })),
});

const errors = ref({});
const processing = ref(false);
const hasResponses = props.survey.has_responses ?? false;

function addQuestion() {
    form.questions.push({ question: '', type: 'text', options: ['', ''] });
}

function removeQuestion(i) {
    form.questions.splice(i, 1);
}

function addOption(q) {
    q.options.push('');
}

function removeOption(q, i) {
    q.options.splice(i, 1);
}

function buildPayload() {
    return {
        ...form,
        questions: form.questions.map((q) => ({
            question: q.question,
            type: q.type,
            options: q.type === 'text' ? [] : q.options.filter((o) => o.trim() !== ''),
        })),
    };
}

function submit() {
    if (processing.value) return;
    processing.value = true;
    errors.value = {};

    const url = props.mode === 'edit'
        ? route('admin.surveys.update', props.survey.id)
        : route('admin.surveys.store');

    const method = props.mode === 'edit' ? 'patch' : 'post';

    router[method](url, buildPayload(), {
        preserveScroll: true,
        onError: (errs) => {
            errors.value = errs;
            processing.value = false;
        },
        onFinish: () => (processing.value = false),
    });
}
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <div>
            <InputLabel for="title" value="Title" />
            <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full" required />
            <InputError class="mt-2" :message="errors.title" />
        </div>
        <div>
            <InputLabel for="description" value="Description (optional)" />
            <textarea id="description" v-model="form.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-maroon-500 focus:ring-maroon-500"></textarea>
        </div>
        <div>
            <InputLabel value="Target audience" />
            <div class="mt-2 flex gap-3">
                <label v-for="a in [{ v: 'all', l: 'All' }, { v: 'alumni', l: 'Alumni' }, { v: 'students', l: 'Students' }]" :key="a.v"
                    :class="['cursor-pointer rounded-lg border px-3 py-1.5 text-sm font-medium transition', form.target_audience === a.v ? 'border-maroon-600 bg-maroon-50 text-maroon-700' : 'border-gray-300 text-gray-700']">
                    <input type="radio" :value="a.v" v-model="form.target_audience" class="sr-only" />
                    {{ a.l }}
                </label>
            </div>
        </div>
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300 text-maroon-600 focus:ring-maroon-500" />
            Active
        </label>

        <div>
            <div class="flex items-center justify-between">
                <InputLabel value="Questions" />
                <span v-if="hasResponses" class="text-xs text-amber-600">⚠ Questions are locked — survey has responses.</span>
            </div>

            <div class="mt-3 space-y-4">
                <div
                    v-for="(q, i) in form.questions"
                    :key="i"
                    class="rounded-lg border border-gray-200 bg-gray-50 p-4"
                >
                    <div class="flex items-start gap-3">
                        <div class="flex-1 space-y-3">
                            <div>
                                <input v-model="q.question" type="text" placeholder="Question text" :disabled="hasResponses"
                                    class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-maroon-500 focus:ring-maroon-500 disabled:bg-gray-100" />
                                <InputError :message="errors[`questions.${i}.question`]" class="mt-1" />
                            </div>
                            <select v-model="q.type" :disabled="hasResponses"
                                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-maroon-500 focus:ring-maroon-500 disabled:bg-gray-100">
                                <option value="text">Text answer</option>
                                <option value="single_choice">Single choice</option>
                                <option value="multi_choice">Multi choice</option>
                            </select>

                            <div v-if="q.type !== 'text' && !hasResponses" class="space-y-1">
                                <p class="text-xs font-medium text-gray-500">Options</p>
                                <div v-for="(_, oi) in q.options" :key="oi" class="flex gap-2">
                                    <input v-model="q.options[oi]" type="text" :placeholder="`Option ${oi + 1}`"
                                        class="flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-maroon-500 focus:ring-maroon-500" />
                                    <button v-if="q.options.length > 2" type="button" class="text-red-500 hover:text-red-700" @click="removeOption(q, oi)">×</button>
                                </div>
                                <button type="button" class="text-xs text-maroon-600 hover:text-maroon-700" @click="addOption(q)">+ Add option</button>
                                <InputError :message="errors[`questions.${i}.options`]" class="mt-1" />
                            </div>
                        </div>
                        <button v-if="!hasResponses" type="button" class="mt-1 text-gray-400 hover:text-red-600" @click="removeQuestion(i)">✕</button>
                    </div>
                </div>
            </div>

            <button v-if="!hasResponses" type="button"
                class="mt-3 rounded-lg border border-dashed border-gray-300 px-4 py-2 text-sm text-gray-600 hover:border-maroon-500 hover:text-maroon-600"
                @click="addQuestion">
                + Add question
            </button>
            <InputError class="mt-2" :message="errors.questions" />
        </div>

        <button type="submit" :disabled="processing"
            class="rounded-lg bg-maroon-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-maroon-700 disabled:opacity-60">
            {{ processing ? 'Saving…' : mode === 'edit' ? 'Update survey' : 'Create survey' }}
        </button>
    </form>
</template>
