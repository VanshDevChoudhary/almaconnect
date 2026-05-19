<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { JOB_TYPE_LABELS } from '@/lib/format';
import { useToast } from '@/Composables/useToast';
import { useConfirm } from '@/Composables/useConfirm';

const props = defineProps({
    jobs: { type: Array, default: () => [] },
});

const { showToast } = useToast();
const { confirm } = useConfirm();
const tab = ref('active');

function bucket(job) {
    if (job.status === 'filled') return 'filled';
    if (job.status === 'expired' || dayjs(job.expires_at).isBefore(dayjs())) return 'expired';
    return 'active';
}

const filtered = computed(() => props.jobs.filter((j) => bucket(j) === tab.value));

async function destroy(id) {
    const ok = await confirm({
        title: 'Delete this job posting?',
        body: 'This cannot be undone.',
        confirmLabel: 'Delete',
    });
    if (!ok) return;
    router.delete(route('jobs.destroy', id), {
        preserveScroll: true,
        onSuccess: () => showToast('Job deleted.'),
    });
}
</script>

<template>
    <Head title="My job posts" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">My job posts</h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="flex gap-6 border-b border-gray-200">
                    <button
                        v-for="t in [
                            { k: 'active', l: 'Active' },
                            { k: 'filled', l: 'Filled' },
                            { k: 'expired', l: 'Expired' },
                        ]"
                        :key="t.k"
                        type="button"
                        :class="[
                            'pb-3 text-sm font-medium transition',
                            tab === t.k ? 'border-b-2 border-maroon-600 text-maroon-600' : 'text-gray-500 hover:text-gray-700',
                        ]"
                        @click="tab = t.k"
                    >
                        {{ t.l }}
                    </button>
                </div>

                <div class="mt-6 space-y-3">
                    <div
                        v-for="j in filtered"
                        :key="j.id"
                        class="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-sm"
                    >
                        <div>
                            <Link :href="route('jobs.show', j.id)" class="font-medium text-gray-900 hover:text-maroon-600">
                                {{ j.title }}
                            </Link>
                            <p class="text-sm text-gray-500">
                                {{ j.company }} · {{ JOB_TYPE_LABELS[j.type] || j.type }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <Link :href="route('jobs.edit', j.id)" class="text-sm font-medium text-maroon-600 hover:text-maroon-700">
                                Edit
                            </Link>
                            <button
                                type="button"
                                class="text-sm font-medium text-red-600 hover:text-red-700"
                                @click="destroy(j.id)"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                    <div v-if="!filtered.length" class="py-16 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" />
                        </svg>
                        <h3 class="mt-3 text-sm font-semibold text-gray-900">
                            {{ tab === 'active' ? "You haven't posted any jobs" : `No ${tab} jobs` }}
                        </h3>
                        <p v-if="tab === 'active'" class="mt-1 text-sm text-gray-500">Share an opportunity with the network.</p>
                        <Link v-if="tab === 'active'" :href="route('jobs.create')" class="mt-4 inline-block rounded-lg bg-maroon-600 px-4 py-2 text-sm font-semibold text-white hover:bg-maroon-700">
                            Post a job
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
