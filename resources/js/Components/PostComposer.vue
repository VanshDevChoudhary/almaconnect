<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { useForm } from '@inertiajs/vue3';
import UserAvatar from '@/Components/UserAvatar.vue';
import { useToast } from '@/Composables/useToast';
import { renderMarkdown } from '@/lib/markdown';

const props = defineProps({
    groupSlug: { type: String, required: true },
    currentUser: { type: Object, required: true },
});

const { showToast } = useToast();

const form = useForm({ body: '', image: null });
const showPreview = ref(false);
const imagePreview = ref(null);
const fileInput = ref(null);
const textarea = ref(null);

const remaining = computed(() => 5000 - form.body.length);
const rendered = computed(() => renderMarkdown(form.body));

watch(
    () => form.body,
    async () => {
        await nextTick();
        const el = textarea.value;
        if (el) {
            el.style.height = 'auto';
            el.style.height = `${el.scrollHeight}px`;
        }
    },
);

function onImage(e) {
    const file = e.target.files?.[0];
    if (!file) return;
    form.image = file;
    const reader = new FileReader();
    reader.onload = (ev) => (imagePreview.value = ev.target.result);
    reader.readAsDataURL(file);
}

function removeImage() {
    form.image = null;
    imagePreview.value = null;
    if (fileInput.value) fileInput.value.value = '';
}

function submit() {
    if (!form.body.trim()) return;
    form.post(route('posts.store', props.groupSlug), {
        preserveScroll: true,
        preserveState: true,
        only: ['posts'],
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            removeImage();
            showPreview.value = false;
            showToast('Posted.');
        },
    });
}
</script>

<template>
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex gap-3">
            <UserAvatar :user="currentUser" size="md" />
            <div class="flex-1">
                <textarea
                    v-if="!showPreview"
                    ref="textarea"
                    v-model="form.body"
                    rows="2"
                    maxlength="5000"
                    placeholder="Write something for the group… (Markdown supported)"
                    class="block w-full resize-none rounded-lg border-gray-300 text-sm shadow-sm transition focus:border-maroon-600 focus:ring-maroon-500"
                ></textarea>
                <div
                    v-else
                    class="prose prose-sm min-h-[4rem] max-w-none rounded-lg border border-gray-200 bg-gray-50 p-3 text-gray-800"
                    v-html="rendered || '<p class=\'text-gray-400\'>Nothing to preview</p>'"
                ></div>

                <p v-if="form.errors.body" class="mt-1 text-sm text-red-600">
                    {{ form.errors.body }}
                </p>

                <div v-if="imagePreview" class="relative mt-3 w-fit">
                    <img :src="imagePreview" class="max-h-48 rounded-lg border border-gray-200" alt="Preview" />
                    <button
                        type="button"
                        class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-gray-900 text-white"
                        @click="removeImage"
                    >
                        &times;
                    </button>
                </div>
                <p v-if="form.errors.image" class="mt-1 text-sm text-red-600">
                    {{ form.errors.image }}
                </p>

                <div class="mt-3 flex items-center justify-between">
                    <div class="flex items-center gap-3 text-sm text-gray-500">
                        <button type="button" class="hover:text-gray-700" @click="fileInput?.click()">
                            📎 Image
                        </button>
                        <input
                            ref="fileInput"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            class="hidden"
                            @change="onImage"
                        />
                        <button
                            type="button"
                            class="hover:text-gray-700"
                            @click="showPreview = !showPreview"
                        >
                            {{ showPreview ? 'Edit' : 'Preview' }}
                        </button>
                        <span class="text-xs text-gray-400">{{ remaining }}</span>
                    </div>
                    <button
                        type="button"
                        :disabled="!form.body.trim() || form.processing"
                        class="rounded-lg bg-maroon-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-maroon-700 disabled:opacity-50"
                        @click="submit"
                    >
                        {{ form.processing ? 'Posting…' : 'Post' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
