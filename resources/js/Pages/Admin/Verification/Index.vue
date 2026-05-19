<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UserAvatar from '@/Components/UserAvatar.vue';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

const props = defineProps({
    pending: { type: Array, default: () => [] },
});

const { showToast } = useToast();
const { confirm } = useConfirm();
const selected = ref([]);

function approve(id) {
    router.post(route('admin.users.approve', id), {}, {
        preserveScroll: true,
        onSuccess: () => showToast('Approved.'),
    });
}
async function reject(id) {
    const ok = await confirm({
        title: 'Reject this user?',
        body: 'The user will be notified their account was rejected.',
        confirmLabel: 'Reject',
    });
    if (!ok) return;
    router.post(route('admin.users.reject', id), {}, {
        preserveScroll: true,
        onSuccess: () => showToast('Rejected.'),
    });
}
function bulkApprove() {
    if (!selected.value.length) return;
    router.post(route('admin.users.bulk-approve'), { ids: selected.value }, {
        preserveScroll: true,
        onSuccess: () => { selected.value = []; showToast('Users approved.'); },
    });
}
async function bulkReject() {
    if (!selected.value.length) return;
    const ok = await confirm({
        title: `Reject ${selected.value.length} users?`,
        body: 'All selected users will be rejected. This cannot be undone.',
        confirmLabel: 'Reject all',
    });
    if (!ok) return;
    router.post(route('admin.users.bulk-reject'), { ids: selected.value }, {
        preserveScroll: true,
        onSuccess: () => { selected.value = []; showToast('Users rejected.'); },
    });
}
</script>

<template>
    <Head title="Verification queue" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Pending verification ({{ pending.length }})</h1>
                <div v-if="selected.length" class="flex gap-2">
                    <button type="button" class="rounded-lg bg-green-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-green-700" @click="bulkApprove">Approve selected</button>
                    <button type="button" class="rounded-lg bg-red-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-red-700" @click="bulkReject">Reject selected</button>
                </div>
            </div>
        </template>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3"><input type="checkbox" @change="(e) => selected = e.target.checked ? pending.map(p => p.id) : []" /></th>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Details</th>
                        <th class="px-4 py-3">Roster match</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="u in pending" :key="u.id">
                        <td class="px-4 py-3"><input type="checkbox" :value="u.id" v-model="selected" /></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <UserAvatar :user="{ id: u.id, name: u.name, avatar: u.avatar }" size="sm" />
                                <div>
                                    <p class="font-medium text-gray-900">{{ u.name }}</p>
                                    <p class="text-xs text-gray-500">{{ u.email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            <span v-if="u.batch">{{ u.batch }}</span>
                            <span v-if="u.branch"> · {{ u.branch }}</span>
                            <span v-if="u.roll_no"> · {{ u.roll_no }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span v-if="u.roster_match" class="text-green-700">✓ Matched</span>
                            <span v-else class="text-amber-600">❓ No match</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button type="button" class="text-sm font-medium text-green-700 hover:text-green-900" @click="approve(u.id)">Approve</button>
                            <button type="button" class="ml-3 text-sm font-medium text-red-600 hover:text-red-800" @click="reject(u.id)">Reject</button>
                        </td>
                    </tr>
                    <tr v-if="!pending.length"><td colspan="5" class="px-4 py-12 text-center text-gray-500">No pending users.</td></tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
