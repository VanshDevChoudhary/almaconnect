<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    app_name: { type: String, default: '' },
    institute_domains: { type: String, default: '' },
    mail_mailer: { type: String, default: '' },
});
</script>

<template>
    <Head title="Settings" />
    <AdminLayout>
        <template #header><h1 class="text-2xl font-bold text-gray-900">Settings</h1></template>
        <div class="max-w-2xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">App name</p>
                <p class="mt-1 text-sm text-gray-900">{{ app_name }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Institute domain(s)</p>
                <p class="mt-1 text-sm text-gray-900">{{ institute_domains }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Mail driver</p>
                <p class="mt-1 text-sm text-gray-900">{{ mail_mailer }}</p>
            </div>
            <p class="text-xs text-amber-600">Full settings editor coming in V2.</p>
        </div>
    </AdminLayout>
</template>
