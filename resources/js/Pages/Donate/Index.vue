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

                <p v-if="!featured" class="mt-16 text-center text-sm text-gray-500">
                    No active campaigns right now. Check back soon.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
