<script setup>
import { reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DirectoryPagination from '@/Components/DirectoryPagination.vue';
import { formatINR } from '@/lib/format';

const props = defineProps({
    donations: { type: Object, required: true },
    campaigns: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const f = reactive({
    status: props.filters.status || 'all',
    campaign_id: props.filters.campaign_id || '',
    from: props.filters.from || '',
    to: props.filters.to || '',
});

function apply() {
    router.get(route('admin.donations.index'), {
        status: f.status !== 'all' ? f.status : undefined,
        campaign_id: f.campaign_id || undefined,
        from: f.from || undefined,
        to: f.to || undefined,
    }, { preserveState: true, preserveScroll: true, replace: true });
}

const statusColor = (s) => ({
    success: 'text-green-700',
    pending: 'text-amber-700',
    failed: 'text-red-700',
    refunded: 'text-gray-500',
}[s] || 'text-gray-700');
</script>

<template>
    <Head title="Donations" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Donations</h2>
        </template>
        <div class="py-10">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-end gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div>
                        <label class="block text-xs font-medium text-gray-500">Status</label>
                        <select v-model="f.status" class="mt-1 rounded-md border-gray-300 text-sm" @change="apply">
                            <option value="all">All</option>
                            <option value="success">Success</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500">Campaign</label>
                        <select v-model="f.campaign_id" class="mt-1 rounded-md border-gray-300 text-sm" @change="apply">
                            <option value="">All</option>
                            <option v-for="c in campaigns" :key="c.id" :value="c.id">{{ c.title }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500">From</label>
                        <input v-model="f.from" type="date" class="mt-1 rounded-md border-gray-300 text-sm" @change="apply" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500">To</label>
                        <input v-model="f.to" type="date" class="mt-1 rounded-md border-gray-300 text-sm" @change="apply" />
                    </div>
                </div>

                <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3">Donor</th>
                                <th class="px-4 py-3">Amount</th>
                                <th class="px-4 py-3">Campaign</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="d in donations.data" :key="d.id">
                                <td class="px-4 py-3 text-gray-500">{{ d.id }}</td>
                                <td class="px-4 py-3 text-gray-900">
                                    {{ d.donor }}
                                    <span v-if="d.is_anonymous" class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-500">anon</span>
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ formatINR(d.amount) }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ d.campaign }}</td>
                                <td class="px-4 py-3 font-medium capitalize" :class="statusColor(d.status)">{{ d.status }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ dayjs(d.created_at).format('MMM D, YYYY') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a v-if="d.has_receipt" :href="route('donate.receipt', d.id)" class="text-indigo-600 hover:text-indigo-700">Receipt</a>
                                </td>
                            </tr>
                            <tr v-if="!donations.data.length"><td colspan="7" class="px-4 py-10 text-center text-gray-500">No donations.</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    <DirectoryPagination :links="donations.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
