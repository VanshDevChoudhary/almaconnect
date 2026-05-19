<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DirectoryPagination from '@/Components/DirectoryPagination.vue';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

const props = defineProps({
    entries: { type: Object, required: true },
    total: { type: Number, default: 0 },
    filters: { type: Object, default: () => ({}) },
});

const { showToast } = useToast();
const { confirm } = useConfirm();
const q = ref(props.filters.q || '');
const replaceAll = ref(false);
const file = ref(null);
const uploading = ref(false);

function search() {
    router.get(route('admin.roster.index'), { q: q.value || undefined }, {
        preserveState: true, preserveScroll: true, replace: true,
    });
}

async function upload() {
    if (!file.value) return;
    if (replaceAll.value) {
        const ok = await confirm({
            title: 'Replace all roster entries?',
            body: 'This will delete every existing entry and replace them with the new CSV. This cannot be undone.',
            confirmLabel: 'Yes, replace all',
        });
        if (!ok) return;
    }
    uploading.value = true;
    const form = new FormData();
    form.append('csv', file.value);
    form.append('replace_all', replaceAll.value ? '1' : '0');
    router.post(route('admin.roster.upload'), form, {
        forceFormData: true,
        onFinish: () => (uploading.value = false),
    });
}

async function destroy(id) {
    const ok = await confirm({
        title: 'Remove this roster entry?',
        body: 'This cannot be undone.',
        confirmLabel: 'Remove',
    });
    if (!ok) return;
    router.delete(route('admin.roster.destroy', id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Roster" />
    <AdminLayout>
        <template #header><h1 class="text-2xl font-bold text-gray-900">Roster</h1></template>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-1 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Upload CSV</h2>
                <p class="mt-1 text-xs text-gray-500">
                    Currently <span class="font-semibold">{{ total.toLocaleString() }}</span> entries.
                </p>
                <p class="mt-2 text-xs text-gray-400">
                    Expected columns: name, email, batch, branch, roll_no
                </p>
                <input type="file" accept=".csv,.txt" class="mt-3 block text-sm" @change="(e) => file = e.target.files[0]" />
                <label class="mt-2 flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" v-model="replaceAll" class="rounded border-gray-300 text-maroon-600 focus:ring-maroon-500" />
                    Replace all existing entries
                </label>
                <button type="button" :disabled="!file || uploading"
                    class="mt-3 w-full rounded-lg bg-maroon-600 px-4 py-2 text-sm font-semibold text-white hover:bg-maroon-700 disabled:opacity-50"
                    @click="upload">
                    {{ uploading ? 'Uploading…' : 'Upload' }}
                </button>
            </div>

            <div class="lg:col-span-2">
                <div class="mb-3 flex gap-2">
                    <input v-model="q" type="text" placeholder="Search name, email, batch…" class="flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-maroon-500 focus:ring-maroon-500" @keydown.enter="search" />
                    <button type="button" class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-200" @click="search">Search</button>
                </div>
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Batch</th><th class="px-4 py-3">Branch</th><th class="px-4 py-3"></th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="e in entries.data" :key="e.id">
                                <td class="px-4 py-2 text-gray-900">{{ e.name }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ e.email || '—' }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ e.batch }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ e.branch }}</td>
                                <td class="px-4 py-2 text-right">
                                    <button type="button" class="text-red-500 hover:text-red-700" @click="destroy(e.id)">×</button>
                                </td>
                            </tr>
                            <tr v-if="!entries.data.length"><td colspan="5" class="px-4 py-10 text-center text-gray-500">No entries.</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4"><DirectoryPagination :links="entries.links" /></div>
            </div>
        </div>
    </AdminLayout>
</template>
