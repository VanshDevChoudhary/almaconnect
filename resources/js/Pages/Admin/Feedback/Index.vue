<script setup>
import { ref, reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DirectoryPagination from '@/Components/DirectoryPagination.vue';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

const props = defineProps({
    feedback: { type: Object, required: true },
    filters: { type: Object, default: () => ({ category: 'all', resolved: 'all' }) },
});

const { showToast } = useToast();
const { confirm } = useConfirm();
const f = reactive({ ...props.filters });
const expanded = ref(null);

function apply() {
    router.get(route('admin.feedback.index'), {
        category: f.category !== 'all' ? f.category : undefined,
        resolved: f.resolved !== 'all' ? f.resolved : undefined,
    }, { preserveState: true, preserveScroll: true, replace: true });
}

function toggle(id) {
    router.post(route('admin.feedback.toggle', id), {}, {
        preserveScroll: true,
        preserveState: true,
        only: ['feedback'],
    });
}

async function destroy(id) {
    const ok = await confirm({
        title: 'Delete this feedback?',
        body: 'This cannot be undone.',
        confirmLabel: 'Delete',
    });
    if (!ok) return;
    router.delete(route('admin.feedback.destroy', id), {
        preserveScroll: true,
        onSuccess: () => showToast('Deleted.'),
    });
}

const categoryColor = { bug: 'text-red-700', suggestion: 'text-blue-700', general: 'text-gray-700' };
</script>

<template>
    <Head title="Feedback inbox" />
    <AdminLayout>
        <template #header><h2 class="text-xl font-semibold leading-tight text-gray-800">Feedback inbox</h2></template>
        <div class="py-10">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-end gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div>
                        <label class="block text-xs font-medium text-gray-500">Category</label>
                        <select v-model="f.category" class="mt-1 rounded-md border-gray-300 text-sm" @change="apply">
                            <option value="all">All</option>
                            <option value="bug">Bug</option>
                            <option value="suggestion">Suggestion</option>
                            <option value="general">General</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500">Status</label>
                        <select v-model="f.resolved" class="mt-1 rounded-md border-gray-300 text-sm" @change="apply">
                            <option value="all">All</option>
                            <option value="unresolved">Unresolved</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 space-y-2">
                    <div
                        v-for="fb in feedback.data"
                        :key="fb.id"
                        class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
                    >
                        <button
                            type="button"
                            class="flex w-full items-center justify-between px-4 py-3 text-left hover:bg-gray-50"
                            @click="expanded = expanded === fb.id ? null : fb.id"
                        >
                            <div class="flex items-center gap-3">
                                <span :class="categoryColor[fb.category]" class="text-xs font-semibold capitalize">{{ fb.category }}</span>
                                <span class="text-sm text-gray-700">{{ fb.name }} — {{ fb.message.slice(0, 60) }}{{ fb.message.length > 60 ? '…' : '' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <span v-if="fb.is_resolved" class="text-green-600">✓ Resolved</span>
                                <span>{{ dayjs(fb.created_at).format('MMM D') }}</span>
                            </div>
                        </button>
                        <div v-if="expanded === fb.id" class="border-t border-gray-100 px-4 py-3">
                            <p class="text-sm text-gray-800 whitespace-pre-line">{{ fb.message }}</p>
                            <p class="mt-2 text-xs text-gray-500">{{ fb.name }} · {{ fb.email }}</p>
                            <div class="mt-3 flex gap-3">
                                <button type="button" class="text-xs font-medium text-maroon-600 hover:text-maroon-700" @click="toggle(fb.id)">
                                    {{ fb.is_resolved ? 'Mark unresolved' : 'Mark resolved' }}
                                </button>
                                <a :href="`mailto:${fb.email}`" class="text-xs font-medium text-gray-600 hover:text-gray-900">Reply via email</a>
                                <button type="button" class="text-xs font-medium text-red-600 hover:text-red-700" @click="destroy(fb.id)">Delete</button>
                            </div>
                        </div>
                    </div>
                    <p v-if="!feedback.data.length" class="py-12 text-center text-sm text-gray-500">No feedback yet.</p>
                </div>

                <div class="mt-6">
                    <DirectoryPagination :links="feedback.links" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
