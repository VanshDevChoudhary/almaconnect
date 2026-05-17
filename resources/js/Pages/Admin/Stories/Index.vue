<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useToast } from '@/Composables/useToast';

const props = defineProps({
    stories: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ status: 'all' }) },
});

const { showToast } = useToast();
const status = ref(props.filters.status || 'all');

function filter() {
    router.get(route('admin.stories.index'), status.value === 'all' ? {} : { status: status.value }, {
        preserveState: true, preserveScroll: true, replace: true,
    });
}
function approve(id) {
    router.post(route('admin.stories.approve', id), {}, { preserveScroll: true, onSuccess: () => showToast('Story published.') });
}
function reject(id) {
    router.post(route('admin.stories.reject', id), {}, { preserveScroll: true, onSuccess: () => showToast('Story rejected.') });
}
function destroy(id) {
    if (!confirm('Delete this story?')) return;
    router.delete(route('admin.stories.destroy', id), { preserveScroll: true, onSuccess: () => showToast('Story deleted.') });
}

const badge = {
    pending: 'bg-amber-50 text-amber-700',
    published: 'bg-green-50 text-green-700',
    rejected: 'bg-red-50 text-red-700',
};
</script>

<template>
    <Head title="Manage stories" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Manage stories</h2>
                <Link :href="route('admin.stories.create')" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                    + New story
                </Link>
            </div>
        </template>
        <div class="py-10">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <select v-model="status" class="rounded-md border-gray-300 text-sm" @change="filter">
                    <option value="all">All</option>
                    <option value="pending">Pending</option>
                    <option value="published">Published</option>
                    <option value="rejected">Rejected</option>
                </select>

                <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr><th class="px-4 py-3">Headline</th><th class="px-4 py-3">Featured</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="s in stories" :key="s.id">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ s.headline }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ s.featured || '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium capitalize" :class="badge[s.status]">{{ s.status }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button v-if="s.status === 'pending'" type="button" class="text-green-600 hover:text-green-700" @click="approve(s.id)">Approve</button>
                                    <button v-if="s.status === 'pending'" type="button" class="ml-3 text-red-600 hover:text-red-700" @click="reject(s.id)">Reject</button>
                                    <Link :href="route('admin.stories.edit', s.id)" class="ml-3 text-indigo-600 hover:text-indigo-700">Edit</Link>
                                    <button type="button" class="ml-3 text-gray-500 hover:text-red-700" @click="destroy(s.id)">Delete</button>
                                </td>
                            </tr>
                            <tr v-if="!stories.length"><td colspan="4" class="px-4 py-10 text-center text-gray-500">No stories.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
