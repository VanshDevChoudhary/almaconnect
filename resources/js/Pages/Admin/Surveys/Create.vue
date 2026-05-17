<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SurveyForm from '@/Components/SurveyForm.vue';
</script>

<template>
    <Head title="New survey" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-xl font-semibold leading-tight text-gray-800">Create survey</h2></template>
        <div class="py-10">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <Link :href="route('admin.surveys.index')" class="text-sm text-gray-500 hover:text-gray-700">← Back</Link>
                <div class="mt-4 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <SurveyForm mode="create" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
