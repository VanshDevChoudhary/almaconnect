<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import UserAvatar from '@/Components/UserAvatar.vue';

dayjs.extend(relativeTime);

const props = defineProps({
    postId: { type: Number, required: true },
    comments: { type: Array, default: () => [] },
    count: { type: Number, default: 0 },
});

const open = ref(false);
const body = ref('');
const submitting = ref(false);

const remaining = computed(() => 1000 - body.value.length);

function submit() {
    if (!body.value.trim() || submitting.value) return;
    submitting.value = true;
    router.post(
        route('comments.store', props.postId),
        { body: body.value },
        {
            preserveScroll: true,
            preserveState: true,
            only: ['posts'],
            onSuccess: () => (body.value = ''),
            onFinish: () => (submitting.value = false),
        },
    );
}

function remove(id) {
    router.delete(route('comments.destroy', id), {
        preserveScroll: true,
        preserveState: true,
        only: ['posts'],
    });
}
</script>

<template>
    <div class="mt-3">
        <button
            type="button"
            class="text-sm text-gray-500 hover:text-gray-700"
            @click="open = !open"
        >
            💬 {{ count }} {{ count === 1 ? 'comment' : 'comments' }}
        </button>

        <Transition
            enter-active-class="transition duration-250 ease-out"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
        >
            <div v-if="open" class="mt-3 space-y-3 border-t border-gray-100 pt-3">
                <div
                    v-for="c in comments"
                    :key="c.id"
                    class="flex items-start gap-2"
                >
                    <UserAvatar
                        :user="{ id: c.author.id, name: c.author.name, avatar: c.author.avatar }"
                        size="sm"
                    />
                    <div class="flex-1 rounded-lg bg-gray-50 px-3 py-2">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-900">
                                {{ c.author.name }}
                                <span class="ml-1 text-xs font-normal text-gray-400">
                                    {{ dayjs(c.created_at).fromNow() }}
                                </span>
                            </p>
                            <button
                                v-if="c.can_delete"
                                type="button"
                                class="text-xs text-gray-400 hover:text-red-600"
                                @click="remove(c.id)"
                            >
                                Delete
                            </button>
                        </div>
                        <p class="mt-0.5 whitespace-pre-line text-sm text-gray-700">{{ c.body }}</p>
                    </div>
                </div>

                <p v-if="!comments.length" class="text-sm text-gray-400">
                    No comments yet. Be the first.
                </p>

                <div class="flex items-end gap-2">
                    <div class="flex-1">
                        <textarea
                            v-model="body"
                            rows="2"
                            maxlength="1000"
                            placeholder="Write a comment…"
                            class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-maroon-500 focus:ring-maroon-500"
                        ></textarea>
                        <p class="mt-0.5 text-right text-xs text-gray-400">{{ remaining }}</p>
                    </div>
                    <button
                        type="button"
                        :disabled="!body.trim() || submitting"
                        class="rounded-lg bg-maroon-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-maroon-700 disabled:opacity-50"
                        @click="submit"
                    >
                        Comment
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
