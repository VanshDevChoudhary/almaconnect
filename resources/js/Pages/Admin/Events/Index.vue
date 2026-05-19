<script setup>
import { Head, Link } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    events: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="Manage events" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Manage events
                </h2>
                <Link
                    :href="route('admin.events.create')"
                    class="rounded-lg bg-maroon-600 px-4 py-2 text-sm font-semibold text-white hover:bg-maroon-700"
                >
                    + New event
                </Link>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3">Title</th>
                                <th class="px-4 py-3">When</th>
                                <th class="px-4 py-3">Going</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="e in events" :key="e.slug">
                                <td class="px-4 py-3">
                                    <Link :href="route('events.show', e.slug)" class="font-medium text-gray-900 hover:text-maroon-600">
                                        {{ e.title }}
                                    </Link>
                                    <span
                                        v-if="e.is_past"
                                        class="ml-2 rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500"
                                    >
                                        past
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ dayjs(e.starts_at).format('MMM D, YYYY h:mm A') }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ e.going_count }}</td>
                                <td class="px-4 py-3 text-right">
                                    <Link
                                        :href="route('admin.events.edit', e.slug)"
                                        class="text-sm font-medium text-maroon-600 hover:text-maroon-700"
                                    >
                                        Edit
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!events.length">
                                <td colspan="4" class="px-4 py-10 text-center text-gray-500">
                                    No events yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
