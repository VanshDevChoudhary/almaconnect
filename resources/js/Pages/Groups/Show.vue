<script setup>
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostComposer from '@/Components/PostComposer.vue';
import PostFeed from '@/Components/PostFeed.vue';
import MembersGrid from '@/Components/MembersGrid.vue';

const props = defineProps({
    group: { type: Object, required: true },
    posts: { type: Object, required: true },
    members: { type: Array, default: () => [] },
    isMember: { type: Boolean, default: false },
    isModerator: { type: Boolean, default: false },
    isCreator: { type: Boolean, default: false },
});

const page = usePage();
const currentUser = page.props.auth.user;
const tab = ref('feed');
const busy = ref(false);

const typeLabels = {
    regional: 'Regional',
    batch: 'Batch',
    interest: 'Interest',
    professional: 'Professional',
};

function join() {
    busy.value = true;
    router.post(route('groups.join', props.group.slug), {}, {
        preserveScroll: true,
        onFinish: () => (busy.value = false),
    });
}
function leave() {
    busy.value = true;
    router.post(route('groups.leave', props.group.slug), {}, {
        preserveScroll: true,
        onFinish: () => (busy.value = false),
    });
}
</script>

<template>
    <Head :title="group.name" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <Link
                    :href="route('groups.index')"
                    class="text-sm text-gray-500 hover:text-gray-700"
                >
                    ← Back to groups
                </Link>

                <div class="mt-3 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div
                        class="h-40 bg-gradient-to-r from-indigo-500 via-indigo-600 to-purple-600"
                        :style="group.cover_image ? { backgroundImage: `url(/storage/${group.cover_image})`, backgroundSize: 'cover' } : {}"
                    ></div>
                    <div class="flex flex-wrap items-start justify-between gap-3 p-5">
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="text-xl font-bold text-gray-900">{{ group.name }}</h1>
                                <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                    {{ typeLabels[group.type] || group.type }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">{{ group.members_count }} members</p>
                            <p v-if="group.description" class="mt-2 max-w-xl text-sm text-gray-600">
                                {{ group.description }}
                            </p>
                        </div>
                        <div>
                            <span
                                v-if="isCreator"
                                class="inline-flex cursor-default rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-500"
                            >
                                You created this group
                            </span>
                            <button
                                v-else-if="isMember"
                                type="button"
                                :disabled="busy"
                                class="rounded-lg bg-green-50 px-4 py-2 text-sm font-medium text-green-700 transition hover:bg-red-50 hover:text-red-700 disabled:opacity-50"
                                @click="leave"
                            >
                                Member ✓ · Leave
                            </button>
                            <button
                                v-else
                                type="button"
                                :disabled="busy"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-50"
                                @click="join"
                            >
                                Join group
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex gap-6 border-b border-gray-200">
                    <button
                        type="button"
                        :class="[
                            'pb-3 text-sm font-medium transition',
                            tab === 'feed'
                                ? 'border-b-2 border-indigo-600 text-indigo-600'
                                : 'text-gray-500 hover:text-gray-700',
                        ]"
                        @click="tab = 'feed'"
                    >
                        Feed
                    </button>
                    <button
                        type="button"
                        :class="[
                            'pb-3 text-sm font-medium transition',
                            tab === 'members'
                                ? 'border-b-2 border-indigo-600 text-indigo-600'
                                : 'text-gray-500 hover:text-gray-700',
                        ]"
                        @click="tab = 'members'"
                    >
                        Members
                    </button>
                </div>

                <div v-show="tab === 'feed'" class="mt-6 space-y-4">
                    <PostComposer
                        v-if="isMember"
                        :group-slug="group.slug"
                        :current-user="currentUser"
                    />
                    <div
                        v-else
                        class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-5 text-center text-sm text-gray-600"
                    >
                        Join this group to post and participate.
                    </div>

                    <PostFeed :posts="posts" />
                </div>

                <div v-show="tab === 'members'" class="mt-6">
                    <MembersGrid :members="members" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
