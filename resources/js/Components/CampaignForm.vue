<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    mode: { type: String, default: 'create' },
    campaign: { type: Object, default: () => ({}) },
});

const form = useForm({
    title: props.campaign.title ?? '',
    description: props.campaign.description ?? '',
    cover_image: null,
    target_amount: props.campaign.target_amount ?? '',
    ends_at: props.campaign.ends_at ?? '',
    is_active: props.campaign.is_active ?? true,
    _method: props.mode === 'edit' ? 'patch' : 'post',
});

const coverPreview = ref(props.campaign.cover_image ? `/storage/${props.campaign.cover_image}` : null);

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
            ? route('admin.campaigns.update', props.campaign.slug)
            : route('admin.campaigns.store'),
        { forceFormData: true },
    );
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
            <InputLabel for="description" value="Description (Markdown)" />
            <textarea id="description" v-model="form.description" rows="6" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-maroon-500 focus:ring-maroon-500" required></textarea>
            <InputError class="mt-2" :message="form.errors.description" />
        </div>
        <div>
            <InputLabel value="Cover image" />
            <img v-if="coverPreview" :src="coverPreview" class="mt-2 max-h-40 rounded-lg border border-gray-200" alt="Cover" />
            <input type="file" accept="image/jpeg,image/png,image/webp" class="mt-2 block text-sm" @change="onCover" />
            <InputError class="mt-2" :message="form.errors.cover_image" />
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <InputLabel for="target_amount" value="Target amount (₹)" />
                <TextInput id="target_amount" v-model="form.target_amount" type="number" min="1000" class="mt-1 block w-full" />
                <InputError class="mt-2" :message="form.errors.target_amount" />
            </div>
            <div>
                <InputLabel for="ends_at" value="Ends at" />
                <input id="ends_at" v-model="form.ends_at" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-maroon-500 focus:ring-maroon-500" />
                <InputError class="mt-2" :message="form.errors.ends_at" />
            </div>
        </div>
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300 text-maroon-600 focus:ring-maroon-500" />
            Active (accepting donations)
        </label>

        <button type="submit" :disabled="form.processing" class="rounded-lg bg-maroon-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-maroon-700 disabled:opacity-60">
            {{ form.processing ? 'Saving…' : mode === 'edit' ? 'Update campaign' : 'Create campaign' }}
        </button>
    </form>
</template>
