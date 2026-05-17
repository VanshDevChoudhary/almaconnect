<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import UserAvatar from '@/Components/UserAvatar.vue';
import LikeButton from '@/Components/LikeButton.vue';
import CommentThread from '@/Components/CommentThread.vue';
import PostActionsMenu from '@/Components/PostActionsMenu.vue';
import { useToast } from '@/Composables/useToast';
import { renderMarkdown } from '@/lib/markdown';

dayjs.extend(relativeTime);

const props = defineProps({
    post: { type: Object, required: true },
});

const { showToast } = useToast();
const confirmingDelete = ref(false);

const rendered = computed(() => renderMarkdown(props.post.body));

function pin() {
    router.post(route('posts.pin', props.post.id), {}, {
        preserveScroll: true, preserveState: true, only: ['posts'],
    });
}
function unpin() {
    router.post(route('posts.unpin', props.post.id), {}, {
        preserveScroll: true, preserveState: true, only: ['posts'],
    });
}
function destroy() {
    router.delete(route('posts.destroy', props.post.id), {
        preserveScroll: true,
        preserveState: true,
        only: ['posts'],
        onSuccess: () => showToast('Deleted.'),
        onFinish: () => (confirmingDelete.value = false),
    });
}
</script>

<template>
    <article
        :id="`post-${post.id}`"
        class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm"
    >
        <div
            v-if="post.is_pinned"
            class="mb-3 inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700"
        >
            📌 Pinned
        </div>

        <div class="flex items-start gap-3">
            <UserAvatar
                :user="{ id: post.author.id, name: post.author.name, avatar: post.author.avatar }"
                size="md"
            />
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-gray-900">
                        {{ post.author.name }}
                        <span class="ml-1 text-xs font-normal text-gray-400">
                            {{ dayjs(post.created_at).fromNow() }}
                        </span>
                    </p>
                    <PostActionsMenu
                        :post="post"
                        @pin="pin"
                        @unpin="unpin"
                        @delete="confirmingDelete = true"
                    />
                </div>

                <div
                    class="prose prose-sm mt-2 max-w-none break-words text-gray-800"
                    v-html="rendered"
                ></div>

                <img
                    v-if="post.image"
                    :src="`/storage/${post.image}`"
                    alt="Post image"
                    class="mt-3 max-h-[480px] w-auto rounded-lg border border-gray-100"
                />

                <div class="mt-4 flex items-center gap-6">
                    <LikeButton
                        :post-id="post.id"
                        :liked="post.liked"
                        :count="post.likes_count"
                    />
                </div>

                <CommentThread
                    :post-id="post.id"
                    :comments="post.comments"
                    :count="post.comments_count"
                />
            </div>
        </div>

        <!-- Delete confirmation -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
        >
            <div
                v-if="confirmingDelete"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
                @click.self="confirmingDelete = false"
            >
                <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
                    <h3 class="text-base font-semibold text-gray-900">Delete this post?</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        This can't be undone. Comments and likes are removed too.
                    </p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900"
                            @click="confirmingDelete = false"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700"
                            @click="destroy"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </article>
</template>
