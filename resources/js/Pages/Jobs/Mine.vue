<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { JOB_TYPE_LABELS } from '@/lib/format';
import { useToast } from '@/Composables/useToast';

const props = defineProps({
    jobs: { type: Array, default: () => [] },
});

const { showToast } = useToast();
const tab = ref('active');

function bucket(job) {
    if (job.status === 'filled') return 'filled';
    if (job.status === 'expired' || dayjs(job.expires_at).isBefore(dayjs())) return 'expired';
    return 'active';
}

const filtered = computed(() => props.jobs.filter((j) => bucket(j) === tab.value));

function destroy(id) {
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
                            tab === t.k ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-500 hover:text-gray-700',
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
                            <Link :href="route('jobs.show', j.id)" class="font-medium text-gray-900 hover:text-indigo-600">
                                {{ j.title }}
                            </Link>
                            <p class="text-sm text-gray-500">
                                {{ j.company }} · {{ JOB_TYPE_LABELS[j.type] || j.type }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <Link :href="route('jobs.edit', j.id)" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
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
                    <p v-if="!filtered.length" class="py-16 text-center text-sm text-gray-500">
                        No {{ tab }} jobs.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
