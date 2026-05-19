<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import UserAvatar from '@/Components/UserAvatar.vue';
import LikeButton from '@/Components/LikeButton.vue';
import CommentThread from '@/Components/CommentThread.vue';
import PostActionsMenu from '@/Components/PostActionsMenu.vue';
import { useToast } from '@/Composables/useToast';
import { useConfirm } from '@/Composables/useConfirm';
import { renderMarkdown } from '@/lib/markdown';

dayjs.extend(relativeTime);

const props = defineProps({
    post: { type: Object, required: true },
});

const { showToast } = useToast();
const { confirm } = useConfirm();

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
async function destroy() {
    const ok = await confirm({
        title: 'Delete this post?',
        body: 'This can\'t be undone. Comments and likes are removed too.',
        confirmLabel: 'Delete',
    });
    if (!ok) return;
    router.delete(route('posts.destroy', props.post.id), {
        preserveScroll: true,
        preserveState: true,
        only: ['posts'],
        onSuccess: () => showToast('Post deleted.'),
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
                        @delete="destroy"
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

    </article>
</template>
