<script setup>
import { Head, Link } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DirectoryPagination from '@/Components/DirectoryPagination.vue';
import { JOB_TYPE_LABELS } from '@/lib/format';

defineProps({ jobs: { type: Object, required: true } });

const statusColor = { active: 'text-green-700', filled: 'text-blue-700', expired: 'text-gray-400' };
</script>

<template>
    <Head title="All jobs" />
    <AdminLayout>
        <template #header><h1 class="text-2xl font-bold text-gray-900">Jobs</h1></template>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr><th class="px-4 py-3">Title</th><th class="px-4 py-3">Company</th><th class="px-4 py-3">Type</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Posted</th><th class="px-4 py-3">Poster</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="j in jobs.data" :key="j.id">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ j.title }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ j.company }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ JOB_TYPE_LABELS[j.type] }}</td>
                        <td class="px-4 py-3 capitalize font-medium" :class="statusColor[j.status]">{{ j.status }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ dayjs(j.created_at).format('MMM D') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ j.poster }}</td>
                    </tr>
                    <tr v-if="!jobs.data.length"><td colspan="6" class="px-4 py-12 text-center text-gray-500">No jobs.</td></tr>
                </tbody>
            </table>
        </div>
        <div class="mt-6"><DirectoryPagination :links="jobs.links" /></div>
    </AdminLayout>
</template>
