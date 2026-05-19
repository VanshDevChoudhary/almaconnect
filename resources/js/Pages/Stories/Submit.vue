<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { renderMarkdown } from '@/lib/markdown';
import { useToast } from '@/Composables/useToast';

const page = usePage();
const { showToast } = useToast();

const form = useForm({
    headline: '',
    category: 'career',
    cover_image: null,
    body: '',
});

const showPreview = ref(false);
const coverPreview = ref(null);
const rendered = computed(() => renderMarkdown(form.body));
const bodyLen = computed(() => form.body.length);

const categories = [
    { v: 'entrepreneurship', l: 'Entrepreneurship' },
    { v: 'research', l: 'Research' },
    { v: 'social_impact', l: 'Social Impact' },
    { v: 'career', l: 'Career' },
    { v: 'other', l: 'Other' },
];

function onCover(e) {
    const f = e.target.files?.[0];
    if (!f) return;
    form.cover_image = f;
    const r = new FileReader();
    r.onload = (ev) => (coverPreview.value = ev.target.result);
    r.readAsDataURL(f);
}

function submit() {
    form.post(route('stories.store'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            coverPreview.value = null;
        },
    });
}

watch(
    () => page.props.flash?.success,
    (v) => {
        if (v) showToast(v);
    },
    { immediate: true },
);
</script>

<template>
    <Head title="Submit your story" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Submit your story</h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <form class="space-y-6" @submit.prevent="submit">
                        <div>
                            <InputLabel for="headline" value="Headline" />
                            <TextInput id="headline" v-model="form.headline" type="text" class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="form.errors.headline" />
                        </div>

                        <div>
                            <InputLabel value="Category" />
                            <div class="mt-2 flex flex-wrap gap-2">
                                <label
                                    v-for="c in categories"
                                    :key="c.v"
                                    :class="[
                                        'cursor-pointer rounded-lg border px-3 py-1.5 text-sm font-medium transition',
                                        form.category === c.v
                                            ? 'border-maroon-600 bg-maroon-50 text-maroon-700'
                                            : 'border-gray-300 text-gray-700 hover:bg-gray-50',
                                    ]"
                                >
                                    <input type="radio" :value="c.v" v-model="form.category" class="sr-only" />
                                    {{ c.l }}
                                </label>
                            </div>
                            <InputError class="mt-2" :message="form.errors.category" />
                        </div>

                        <div>
                            <InputLabel value="Cover image (16:9 recommended)" />
                            <img v-if="coverPreview" :src="coverPreview" class="mt-2 aspect-video w-full rounded-lg border border-gray-200 object-cover" alt="Cover" />
                            <input type="file" accept="image/jpeg,image/png,image/webp" class="mt-2 block text-sm" @change="onCover" />
                            <InputError class="mt-2" :message="form.errors.cover_image" />
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <InputLabel for="body" value="Your story (Markdown)" />
                                <button type="button" class="text-xs text-maroon-600 hover:text-maroon-700" @click="showPreview = !showPreview">
                                    {{ showPreview ? 'Edit' : 'Preview' }}
                                </button>
                            </div>
                            <textarea
                                v-if="!showPreview"
                                id="body"
                                v-model="form.body"
                                rows="14"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-maroon-500 focus:ring-maroon-500"
                                required
                            ></textarea>
                            <div v-else class="prose prose-sm mt-1 max-w-none rounded-md border border-gray-200 bg-gray-50 p-4" v-html="rendered"></div>
                            <div class="mt-1 flex justify-between text-xs text-gray-500">
                                <InputError :message="form.errors.body" />
                                <span :class="bodyLen < 500 ? 'text-amber-600' : ''">{{ bodyLen }} / 50000 (min 500)</span>
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-maroon-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-maroon-700 disabled:opacity-60"
                        >
                            {{ form.processing ? 'Submitting…' : 'Submit for review' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
