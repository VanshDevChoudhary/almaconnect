<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CampaignProgress from '@/Components/CampaignProgress.vue';
import DonationModal from '@/Components/DonationModal.vue';
import { renderMarkdown } from '@/lib/markdown';
import { formatINR } from '@/lib/format';

const props = defineProps({
    campaign: { type: Object, required: true },
    recentDonors: { type: Array, default: () => [] },
});

const modalOpen = ref(false);
const rendered = computed(() => renderMarkdown(props.campaign.description));
const daysLeft = computed(() => {
    if (!props.campaign.ends_at) return null;
    const d = dayjs(props.campaign.ends_at).diff(dayjs(), 'day');
    return d >= 0 ? d : 0;
});
</script>

<template>
    <Head :title="campaign.title" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <Link :href="route('donate.index')" class="text-sm text-gray-500 hover:text-gray-700">
                    ← All campaigns
                </Link>

                <div
                    class="mt-4 h-56 w-full overflow-hidden rounded-xl bg-gradient-to-r from-maroon-500 via-maroon-600 to-maroon-600"
                    :style="campaign.cover_image ? { backgroundImage: `url(/storage/${campaign.cover_image})`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}"
                ></div>

                <h1 class="mt-6 text-2xl font-bold text-gray-900">{{ campaign.title }}</h1>

                <div class="mt-6 grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <div class="prose prose-sm max-w-none text-gray-800">
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">About this campaign</h2>
                            <div class="mt-2" v-html="rendered"></div>
                        </div>

                        <div class="mt-8">
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Recent donors</h2>
                            <ul class="mt-3 divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
                                <li
                                    v-for="(d, i) in recentDonors"
                                    :key="i"
                                    class="flex items-center justify-between px-4 py-3 text-sm"
                                >
                                    <span class="text-gray-700">{{ d.name }}</span>
                                    <span class="font-medium text-gray-900">{{ formatINR(d.amount) }}</span>
                                </li>
                                <li v-if="!recentDonors.length" class="px-4 py-6 text-center text-sm text-gray-400">
                                    Be the first to donate.
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div class="sticky top-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <p class="text-lg font-bold text-gray-900">
                                {{ formatINR(campaign.raised_amount) }}
                                <span v-if="campaign.target_amount" class="text-sm font-normal text-gray-500">
                                    raised of {{ formatINR(campaign.target_amount) }}
                                </span>
                            </p>
                            <div class="mt-3">
                                <CampaignProgress :raised="campaign.raised_amount" :target="campaign.target_amount" />
                            </div>
                            <p class="mt-3 text-sm text-gray-600">
                                {{ campaign.donor_count }} donor{{ campaign.donor_count === 1 ? '' : 's' }}
                            </p>
                            <p v-if="daysLeft !== null" class="text-sm text-gray-600">
                                {{ daysLeft }} days left
                            </p>
                            <button
                                type="button"
                                class="mt-5 w-full rounded-lg bg-maroon-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-maroon-700"
                                @click="modalOpen = true"
                            >
                                Donate now
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <DonationModal :campaign="campaign" :open="modalOpen" @close="modalOpen = false" />
    </AuthenticatedLayout>
</template>
