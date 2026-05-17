<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { renderMarkdown } from '@/lib/markdown';

const props = defineProps({
    mode: { type: String, default: 'create' },
    story: { type: Object, default: () => ({}) },
    alumni: { type: Array, default: () => [] },
});

const form = useForm({
    headline: props.story.headline ?? '',
    category: props.story.category ?? 'career',
    cover_image: null,
    body: props.story.body ?? '',
    user_id: props.story.user_id ?? (props.alumni[0]?.id ?? ''),
    status: props.story.status ?? 'published',
    _method: props.mode === 'edit' ? 'patch' : 'post',
});

const showPreview = ref(false);
const rendered = computed(() => renderMarkdown(form.body));
const coverPreview = ref(props.story.cover_image ? `/storage/${props.story.cover_image}` : null);

function onCover(e) {
    const f = e.target.files?.[0];
    if (!f) return;
    form.cover_image = f;
    const r = new FileReader();
    r.onload = (ev) => (coverPreview.value = ev.target.result);
    r.readAsDataURL(f);
}

function submit() {
    form.post(
        props.mode === 'edit'
            ? route('admin.stories.update', props.story.id)
            : route('admin.stories.store'),
        { forceFormData: true },
    );
}
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <div>
            <InputLabel for="headline" value="Headline" />
            <TextInput id="headline" v-model="form.headline" type="text" class="mt-1 block w-full" required />
            <InputError class="mt-2" :message="form.errors.headline" />
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <InputLabel for="user_id" value="Featured alumnus" />
                <select id="user_id" v-model="form.user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option v-for="a in alumni" :key="a.id" :value="a.id">{{ a.name }}</option>
                </select>
                <InputError class="mt-2" :message="form.errors.user_id" />
            </div>
            <div>
                <InputLabel for="category" value="Category" />
                <select id="category" v-model="form.category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="entrepreneurship">Entrepreneurship</option>
                    <option value="research">Research</option>
                    <option value="social_impact">Social Impact</option>
                    <option value="career">Career</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </div>
        <div>
            <InputLabel value="Cover image" />
            <img v-if="coverPreview" :src="coverPreview" class="mt-2 aspect-video w-full rounded-lg border border-gray-200 object-cover" alt="Cover" />
            <input type="file" accept="image/jpeg,image/png,image/webp" class="mt-2 block text-sm" @change="onCover" />
            <InputError class="mt-2" :message="form.errors.cover_image" />
        </div>
        <div>
            <div class="flex items-center justify-between">
                <InputLabel for="body" value="Body (Markdown)" />
                <button type="button" class="text-xs text-indigo-600 hover:text-indigo-700" @click="showPreview = !showPreview">
                    {{ showPreview ? 'Edit' : 'Preview' }}
                </button>
            </div>
            <textarea v-if="!showPreview" id="body" v-model="form.body" rows="12" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required></textarea>
            <div v-else class="prose prose-sm mt-1 max-w-none rounded-md border border-gray-200 bg-gray-50 p-4" v-html="rendered"></div>
            <InputError class="mt-2" :message="form.errors.body" />
        </div>
        <div class="max-w-xs">
            <InputLabel for="status" value="Status" />
            <select id="status" v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="published">Published</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <button type="submit" :disabled="form.processing" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
            {{ form.processing ? 'Saving…' : mode === 'edit' ? 'Update story' : 'Create story' }}
        </button>
    </form>
</template>
