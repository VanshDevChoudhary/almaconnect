<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UserAvatar from '@/Components/UserAvatar.vue';
import { renderMarkdown } from '@/lib/markdown';
import { formatSalary, JOB_TYPE_LABELS } from '@/lib/format';
import { useToast } from '@/Composables/useToast';
import { useConfirm } from '@/Composables/useConfirm';

dayjs.extend(relativeTime);

const props = defineProps({
    job: { type: Object, required: true },
    canManage: { type: Boolean, default: false },
});

const { showToast } = useToast();
const { confirm } = useConfirm();

const salary = computed(() =>
    formatSalary(props.job.salary_min, props.job.salary_max, props.job.salary_currency),
);
const rendered = computed(() => renderMarkdown(props.job.description));
const isFilled = computed(() => props.job.status === 'filled');
const isExpired = computed(() => props.job.is_expired);
const canApply = computed(() => !isFilled.value && !isExpired.value);
const expiresIn = computed(() => dayjs(props.job.expires_at).fromNow());

const applyHref = computed(() =>
    props.job.apply_url
        ? props.job.apply_url
        : props.job.apply_email
          ? `mailto:${props.job.apply_email}`
          : null,
);
const applyExternal = computed(() => !!props.job.apply_url);

function markFilled() {
    router.post(route('jobs.mark-filled', props.job.id), {}, {
        preserveScroll: true,
        onSuccess: () => showToast('Marked as filled.'),
    });
}
async function destroy() {
    const ok = await confirm({
        title: 'Delete this job?',
        body: "This can't be undone.",
        confirmLabel: 'Delete',
    });
    if (!ok) return;
    router.delete(route('jobs.destroy', props.job.id), {
        onSuccess: () => showToast('Job deleted.'),
    });
}
</script>

<template>
    <Head :title="job.title" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <Link :href="route('jobs.index')" class="text-sm text-gray-500 hover:text-gray-700">
                        ← Back to jobs
                    </Link>
                    <div v-if="canManage" class="flex gap-2">
                        <Link :href="route('jobs.edit', job.id)" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Edit
                        </Link>
                        <button
                            v-if="job.status === 'active'"
                            type="button"
                            class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            @click="markFilled"
                        >
                            Mark filled
                        </button>
                        <button
                            type="button"
                            class="rounded-lg border border-red-300 px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50"
                            @click="destroy"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <div
                    v-if="isFilled"
                    class="mt-4 rounded-lg bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800"
                >
                    This role has been filled.
                </div>
                <div
                    v-else-if="isExpired"
                    class="mt-4 rounded-lg bg-gray-100 px-4 py-3 text-sm font-medium text-gray-700"
                >
                    This listing has expired.
                </div>

                <div class="mt-4 grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <h1 class="text-2xl font-bold text-gray-900">{{ job.title }}</h1>
                        <p class="mt-1 text-gray-600">
                            {{ job.company }}<span v-if="job.location"> · {{ job.location }}</span>
                        </p>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-maroon-50 px-2.5 py-0.5 text-xs font-medium text-maroon-700">
                                {{ JOB_TYPE_LABELS[job.type] || job.type }}
                            </span>
                            <span v-if="salary" class="text-sm font-medium text-gray-700">{{ salary }}</span>
                        </div>

                        <div class="prose prose-sm mt-6 max-w-none text-gray-800">
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">About the role</h2>
                            <div class="mt-2" v-html="rendered"></div>
                        </div>

                        <div v-if="job.skills.length" class="mt-6">
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Skills</h2>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                <span v-for="s in job.skills" :key="s" class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-700">{{ s }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div class="sticky top-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <a
                                v-if="canApply && applyHref"
                                :href="applyHref"
                                :target="applyExternal ? '_blank' : undefined"
                                :rel="applyExternal ? 'noopener nofollow' : undefined"
                                class="block w-full rounded-lg bg-maroon-600 px-4 py-2.5 text-center text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-maroon-700 hover:shadow"
                            >
                                Apply now
                            </a>
                            <p v-else class="rounded-lg bg-gray-100 px-4 py-2.5 text-center text-sm text-gray-500">
                                Applications closed
                            </p>

                            <div class="mt-5 border-t border-gray-100 pt-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Posted by</p>
                                <Link
                                    :href="job.poster.slug ? route('profile.show', job.poster.slug) : '#'"
                                    class="mt-2 flex items-center gap-2"
                                >
                                    <UserAvatar :user="{ id: job.poster.id, name: job.poster.name, avatar: job.poster.avatar }" size="sm" />
                                    <span class="text-sm">
                                        <span class="font-medium text-gray-900">{{ job.poster.name }}</span>
                                        <span v-if="job.poster.branch" class="block text-xs text-gray-500">
                                            {{ job.poster.branch }} · {{ job.poster.batch }}
                                        </span>
                                    </span>
                                </Link>
                                <p class="mt-3 text-xs text-gray-500">
                                    Posted {{ dayjs(job.created_at).fromNow() }}
                                </p>
                                <p v-if="!isExpired" class="text-xs text-gray-500">
                                    Expires {{ expiresIn }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
