<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import JobForm from '@/Components/JobForm.vue';

defineProps({
    defaultExpiry: { type: String, default: '' },
});
</script>

<template>
    <Head title="Post a job" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Post a job</h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <Link :href="route('jobs.index')" class="text-sm text-gray-500 hover:text-gray-700">← Back to jobs</Link>
                <div class="mt-4 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <JobForm mode="create" :default-expiry="defaultExpiry" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
