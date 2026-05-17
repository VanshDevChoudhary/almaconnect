<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminStoryForm from '@/Components/AdminStoryForm.vue';

defineProps({ alumni: { type: Array, default: () => [] } });
</script>

<template>
    <Head title="New story" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Create story</h2>
        </template>
        <div class="py-10">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <Link :href="route('admin.stories.index')" class="text-sm text-gray-500 hover:text-gray-700">← Back</Link>
                <div class="mt-4 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <AdminStoryForm mode="create" :alumni="alumni" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
