<script setup>
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UserAvatar from '@/Components/UserAvatar.vue';
import DirectoryPagination from '@/Components/DirectoryPagination.vue';
import { useToast } from '@/Composables/useToast';
import { useConfirm } from '@/Composables/useConfirm';

const props = defineProps({
    users: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const { showToast } = useToast();
const { confirm } = useConfirm();
const f = reactive({ ...props.filters });

function apply() {
    router.get(route('admin.users.index'), {
        role: f.role !== 'all' ? f.role : undefined,
        status: f.status !== 'all' ? f.status : undefined,
        q: f.q || undefined,
    }, { preserveState: true, preserveScroll: true, replace: true });
}

async function destroy(id) {
    const ok = await confirm({
        title: 'Delete this user?',
        body: 'This permanently removes the user and all their data. This cannot be undone.',
        confirmLabel: 'Delete user',
    });
    if (!ok) return;
    router.delete(route('admin.users.destroy', id), {
        preserveScroll: true,
        onSuccess: () => showToast('User deleted.'),
    });
}

const statusColor = { approved: 'text-green-700', pending: 'text-amber-700', rejected: 'text-red-600', banned: 'text-gray-500' };
</script>

<template>
    <Head title="Users" />
    <AdminLayout>
        <template #header><h1 class="text-2xl font-bold text-gray-900">Users</h1></template>

        <div class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div>
                <label class="block text-xs font-medium text-gray-500">Role</label>
                <select v-model="f.role" class="mt-1 rounded-md border-gray-300 text-sm" @change="apply">
                    <option value="all">All</option>
                    <option value="alumni">Alumni</option>
                    <option value="student">Student</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500">Status</label>
                <select v-model="f.status" class="mt-1 rounded-md border-gray-300 text-sm" @change="apply">
                    <option value="all">All</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="banned">Banned</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500">Search</label>
                <input v-model="f.q" type="text" class="mt-1 rounded-md border-gray-300 text-sm" placeholder="Name or email" @keydown.enter="apply" />
            </div>
            <button type="button" class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-200" @click="apply">Apply</button>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr><th class="px-4 py-3">User</th><th class="px-4 py-3">Role</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Joined</th><th class="px-4 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="u in users.data" :key="u.id">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <UserAvatar :user="{ id: u.id, name: u.name, avatar: u.avatar }" size="sm" />
                                <div>
                                    <p class="font-medium text-gray-900">{{ u.name }}</p>
                                    <p class="text-xs text-gray-500">{{ u.email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 capitalize text-gray-600">{{ u.role }}</td>
                        <td class="px-4 py-3 capitalize font-medium" :class="statusColor[u.status]">{{ u.status }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ dayjs(u.created_at).format('MMM D, YYYY') }}</td>
                        <td class="px-4 py-3 text-right">
                            <Link v-if="u.profile_slug" :href="route('profile.show', u.profile_slug)" class="text-indigo-600 hover:text-indigo-700">View</Link>
                            <Link :href="route('admin.users.edit', u.id)" class="ml-3 text-gray-600 hover:text-gray-900">Edit</Link>
                            <button type="button" class="ml-3 text-red-600 hover:text-red-700" @click="destroy(u.id)">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="!users.data.length"><td colspan="5" class="px-4 py-12 text-center text-gray-500">No users.</td></tr>
                </tbody>
            </table>
        </div>
        <div class="mt-6"><DirectoryPagination :links="users.links" /></div>
    </AdminLayout>
</template>
