<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CampaignCard from '@/Components/CampaignCard.vue';

defineProps({
    featured: { type: Object, default: null },
    campaigns: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="Donate" />

    <AuthenticatedLayout>
        <div class="py-10">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold text-gray-900">Support our institute</h1>
                <p class="mt-1 text-sm text-gray-600">Give back to the place that shaped you.</p>

                <div v-if="featured" class="mt-8">
                    <CampaignCard :campaign="featured" featured />
                </div>

                <div v-if="campaigns.length" class="mt-10">
                    <h2 class="text-lg font-semibold text-gray-900">Other campaigns</h2>
                    <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <CampaignCard v-for="c in campaigns" :key="c.slug" :campaign="c" />
                    </div>
                </div>

                <div v-if="!featured && !campaigns.length" class="mt-16 text-center">
                    <svg class="mx-auto h-14 w-14 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                    </svg>
                    <h3 class="mt-3 text-base font-semibold text-gray-900">No active campaigns right now</h3>
                    <p class="mt-1 text-sm text-gray-500">Check back soon — campaigns will appear here.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
