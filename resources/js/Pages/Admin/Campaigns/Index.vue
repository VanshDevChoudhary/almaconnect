<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { formatINR } from '@/lib/format';
import { useToast } from '@/Composables/useToast';

defineProps({ campaigns: { type: Array, default: () => [] } });

const { showToast } = useToast();

function destroy(slug) {
    if (!confirm('Delete this campaign?')) return;
    router.delete(route('admin.campaigns.destroy', slug), {
        onSuccess: () => showToast('Campaign deleted.'),
    });
}
</script>

<template>
    <Head title="Manage campaigns" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Manage campaigns</h2>
                <Link :href="route('admin.campaigns.create')" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                    + New campaign
                </Link>
            </div>
        </template>
        <div class="py-10">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr><th class="px-4 py-3">Title</th><th class="px-4 py-3">Raised / Target</th><th class="px-4 py-3">Active</th><th class="px-4 py-3"></th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="c in campaigns" :key="c.slug">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ c.title }}</td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ formatINR(c.raised_amount) }}<span v-if="c.target_amount"> / {{ formatINR(c.target_amount) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span :class="c.is_active ? 'text-green-700' : 'text-gray-400'">{{ c.is_active ? 'Yes' : 'No' }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link :href="route('admin.campaigns.edit', c.slug)" class="text-indigo-600 hover:text-indigo-700">Edit</Link>
                                    <button type="button" class="ml-3 text-red-600 hover:text-red-700" @click="destroy(c.slug)">Delete</button>
                                </td>
                            </tr>
                            <tr v-if="!campaigns.length"><td colspan="4" class="px-4 py-10 text-center text-gray-500">No campaigns yet.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
