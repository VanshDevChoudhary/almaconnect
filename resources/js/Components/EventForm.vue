<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    mode: { type: String, default: 'create' }, // 'create' | 'edit'
    event: { type: Object, default: () => ({}) },
});

const form = useForm({
    title: props.event.title ?? '',
    description: props.event.description ?? '',
    cover_image: null,
    starts_at: props.event.starts_at ?? '',
    ends_at: props.event.ends_at ?? '',
    location: props.event.location ?? '',
    online_url: props.event.online_url ?? '',
    capacity: props.event.capacity ?? '',
    _method: props.mode === 'edit' ? 'patch' : 'post',
});

const coverPreview = ref(
    props.event.cover_image ? `/storage/${props.event.cover_image}` : null,
);

function onCover(e) {
    const file = e.target.files?.[0];
    if (!file) return;
    form.cover_image = file;
    const r = new FileReader();
    r.onload = (ev) => (coverPreview.value = ev.target.result);
    r.readAsDataURL(file);
}

function submit() {
    const url =
        props.mode === 'edit'
            ? route('admin.events.update', props.event.slug)
            : route('admin.events.store');

    form.post(url, { forceFormData: true });
}
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <div>
            <InputLabel for="title" value="Title" />
            <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full" required />
            <InputError class="mt-2" :message="form.errors.title" />
        </div>

        <div>
            <InputLabel for="description" value="Description (Markdown supported)" />
            <textarea
                id="description"
                v-model="form.description"
                rows="6"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required
            ></textarea>
            <InputError class="mt-2" :message="form.errors.description" />
        </div>

        <div>
            <InputLabel value="Cover image" />
            <img
                v-if="coverPreview"
                :src="coverPreview"
                class="mt-2 max-h-48 rounded-lg border border-gray-200"
                alt="Cover preview"
            />
            <input
                type="file"
                accept="image/jpeg,image/png,image/webp"
                class="mt-2 block text-sm"
                @change="onCover"
            />
            <InputError class="mt-2" :message="form.errors.cover_image" />
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <InputLabel for="starts_at" value="Starts at" />
                <input
                    id="starts_at"
                    v-model="form.starts_at"
                    type="datetime-local"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    required
                />
                <InputError class="mt-2" :message="form.errors.starts_at" />
            </div>
            <div>
                <InputLabel for="ends_at" value="Ends at (optional)" />
                <input
                    id="ends_at"
                    v-model="form.ends_at"
                    type="datetime-local"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
                <InputError class="mt-2" :message="form.errors.ends_at" />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <InputLabel for="location" value="Location (optional)" />
                <TextInput id="location" v-model="form.location" type="text" class="mt-1 block w-full" />
                <InputError class="mt-2" :message="form.errors.location" />
            </div>
            <div>
                <InputLabel for="online_url" value="Online URL (optional)" />
                <TextInput id="online_url" v-model="form.online_url" type="url" class="mt-1 block w-full" />
                <InputError class="mt-2" :message="form.errors.online_url" />
            </div>
        </div>

        <div class="max-w-xs">
            <InputLabel for="capacity" value="Capacity (optional)" />
            <TextInput id="capacity" v-model="form.capacity" type="number" min="1" max="10000" class="mt-1 block w-full" />
            <InputError class="mt-2" :message="form.errors.capacity" />
        </div>

        <div class="flex items-center gap-3">
            <button
                type="submit"
                :disabled="form.processing"
                class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60"
            >
                {{ form.processing ? 'Saving…' : mode === 'edit' ? 'Update event' : 'Create event' }}
            </button>
        </div>
    </form>
</template>
