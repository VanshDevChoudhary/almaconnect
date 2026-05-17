<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Admin" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Admin
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Admin console
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Placeholder. The verification queue and admin tools land
                        in Phase 11.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
