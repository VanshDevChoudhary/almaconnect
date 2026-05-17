<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import SkillsTagInput from '@/Components/SkillsTagInput.vue';
import { renderMarkdown } from '@/lib/markdown';

const props = defineProps({
    mode: { type: String, default: 'create' },
    job: { type: Object, default: () => ({}) },
    defaultExpiry: { type: String, default: '' },
});

const isINR = (c) => c === 'INR';

// INR is stored in rupees but entered/shown in lakhs (LPA).
function toInput(value, currency) {
    if (value == null) return '';
    return isINR(currency) ? value / 100000 : value;
}

const initialCurrency = props.job.salary_currency || 'INR';

const form = useForm({
    title: props.job.title ?? '',
    company: props.job.company ?? '',
    location: props.job.location ?? '',
    type: props.job.type ?? 'full_time',
    description: props.job.description ?? '',
    skills: [...(props.job.skills ?? [])],
    salary_min: toInput(props.job.salary_min, initialCurrency),
    salary_max: toInput(props.job.salary_max, initialCurrency),
    salary_currency: initialCurrency,
    apply_url: props.job.apply_url ?? '',
    apply_email: props.job.apply_email ?? '',
    expires_at: props.job.expires_at ?? props.defaultExpiry,
    _method: props.mode === 'edit' ? 'patch' : 'post',
});

const showPreview = ref(false);
const rendered = computed(() => renderMarkdown(form.description));

function submit() {
    form
        .transform((data) => {
            const out = { ...data };
            const mult = isINR(data.salary_currency) ? 100000 : 1;
            out.salary_min = data.salary_min === '' || data.salary_min == null
                ? null
                : Math.round(Number(data.salary_min) * mult);
            out.salary_max = data.salary_max === '' || data.salary_max == null
                ? null
                : Math.round(Number(data.salary_max) * mult);
            return out;
        })
        .post(
            props.mode === 'edit'
                ? route('jobs.update', props.job.id)
                : route('jobs.store'),
        );
}
</script>

<template>
    <form class="space-y-8" @submit.prevent="submit">
        <section class="space-y-5">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Basics</h3>
            <div>
                <InputLabel for="title" value="Title" />
                <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full" required />
                <InputError class="mt-2" :message="form.errors.title" />
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <InputLabel for="company" value="Company" />
                    <TextInput id="company" v-model="form.company" type="text" class="mt-1 block w-full" required />
                    <InputError class="mt-2" :message="form.errors.company" />
                </div>
                <div>
                    <InputLabel for="location" value="Location" />
                    <TextInput id="location" v-model="form.location" type="text" class="mt-1 block w-full" placeholder="Remote / Bangalore" />
                    <InputError class="mt-2" :message="form.errors.location" />
                </div>
            </div>
            <div>
                <InputLabel value="Type" />
                <div class="mt-2 flex flex-wrap gap-2">
                    <label
                        v-for="t in [
                            { v: 'full_time', l: 'Full-time' },
                            { v: 'internship', l: 'Internship' },
                            { v: 'contract', l: 'Contract' },
                            { v: 'part_time', l: 'Part-time' },
                        ]"
                        :key="t.v"
                        :class="[
                            'cursor-pointer rounded-lg border px-4 py-2 text-sm font-medium transition',
                            form.type === t.v
                                ? 'border-indigo-600 bg-indigo-50 text-indigo-700'
                                : 'border-gray-300 text-gray-700 hover:bg-gray-50',
                        ]"
                    >
                        <input type="radio" :value="t.v" v-model="form.type" class="sr-only" />
                        {{ t.l }}
                    </label>
                </div>
                <InputError class="mt-2" :message="form.errors.type" />
            </div>
            <div>
                <div class="flex items-center justify-between">
                    <InputLabel for="description" value="Description (Markdown)" />
                    <button type="button" class="text-xs text-indigo-600 hover:text-indigo-700" @click="showPreview = !showPreview">
                        {{ showPreview ? 'Edit' : 'Preview' }}
                    </button>
                </div>
                <textarea
                    v-if="!showPreview"
                    id="description"
                    v-model="form.description"
                    rows="8"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    required
                ></textarea>
                <div v-else class="prose prose-sm mt-1 max-w-none rounded-md border border-gray-200 bg-gray-50 p-3" v-html="rendered"></div>
                <InputError class="mt-2" :message="form.errors.description" />
            </div>
            <div>
                <InputLabel value="Skills" />
                <SkillsTagInput v-model="form.skills" :max="15" class="mt-1" />
                <InputError class="mt-2" :message="form.errors.skills" />
            </div>
        </section>

        <section class="space-y-5">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Compensation</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <InputLabel for="salary_min" :value="form.salary_currency === 'INR' ? 'Min (LPA)' : 'Min'" />
                    <TextInput id="salary_min" v-model="form.salary_min" type="number" min="0" class="mt-1 block w-full" />
                    <InputError class="mt-2" :message="form.errors.salary_min" />
                </div>
                <div>
                    <InputLabel for="salary_max" :value="form.salary_currency === 'INR' ? 'Max (LPA)' : 'Max'" />
                    <TextInput id="salary_max" v-model="form.salary_max" type="number" min="0" class="mt-1 block w-full" />
                    <InputError class="mt-2" :message="form.errors.salary_max" />
                </div>
                <div>
                    <InputLabel for="salary_currency" value="Currency" />
                    <select id="salary_currency" v-model="form.salary_currency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option>INR</option>
                        <option>USD</option>
                        <option>EUR</option>
                        <option>GBP</option>
                    </select>
                </div>
            </div>
        </section>

        <section class="space-y-5">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">How to apply</h3>
            <p class="text-xs text-gray-500">Provide at least one of the two.</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <InputLabel for="apply_url" value="Apply URL" />
                    <TextInput id="apply_url" v-model="form.apply_url" type="url" class="mt-1 block w-full" placeholder="https://…" />
                    <InputError class="mt-2" :message="form.errors.apply_url" />
                </div>
                <div>
                    <InputLabel for="apply_email" value="Apply email" />
                    <TextInput id="apply_email" v-model="form.apply_email" type="email" class="mt-1 block w-full" placeholder="careers@company.com" />
                    <InputError class="mt-2" :message="form.errors.apply_email" />
                </div>
            </div>
        </section>

        <section class="space-y-5">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Validity</h3>
            <div class="max-w-xs">
                <InputLabel for="expires_at" value="Expires at" />
                <input
                    id="expires_at"
                    v-model="form.expires_at"
                    type="date"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    required
                />
                <InputError class="mt-2" :message="form.errors.expires_at" />
            </div>
        </section>

        <button
            type="submit"
            :disabled="form.processing"
            class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60"
        >
            {{ form.processing ? 'Saving…' : mode === 'edit' ? 'Update job' : 'Post job' }}
        </button>
    </form>
</template>
