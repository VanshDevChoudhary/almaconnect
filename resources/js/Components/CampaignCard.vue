<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import CampaignProgress from '@/Components/CampaignProgress.vue';
import { formatINR } from '@/lib/format';

const props = defineProps({
    campaign: { type: Object, required: true },
    featured: { type: Boolean, default: false },
});

const daysLeft = computed(() => {
    if (!props.campaign.ends_at) return null;
    const d = dayjs(props.campaign.ends_at).diff(dayjs(), 'day');
    return d >= 0 ? d : 0;
});
</script>

<template>
    <Link
        :href="route('donate.show', campaign.slug)"
        class="block overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md"
    >
        <div
            :class="featured ? 'h-56' : 'h-32'"
            class="w-full bg-gradient-to-r from-maroon-500 via-maroon-600 to-maroon-600"
            :style="campaign.cover_image ? { backgroundImage: `url(/storage/${campaign.cover_image})`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}"
        ></div>
        <div class="p-5">
            <h3 :class="featured ? 'text-xl' : 'text-base'" class="font-semibold text-gray-900">
                {{ campaign.title }}
            </h3>
            <p v-if="featured" class="mt-1 line-clamp-2 text-sm text-gray-600">
                {{ String(campaign.description || '').replace(/[#*_`>\[\]]/g, '').slice(0, 140) }}
            </p>

            <div class="mt-4">
                <CampaignProgress :raised="campaign.raised_amount" :target="campaign.target_amount" />
                <p class="mt-2 text-sm text-gray-700">
                    <span class="font-semibold">{{ formatINR(campaign.raised_amount) }}</span>
                    <span v-if="campaign.target_amount" class="text-gray-500"> raised of {{ formatINR(campaign.target_amount) }}</span>
                </p>
                <p class="mt-1 text-xs text-gray-500">
                    {{ campaign.donor_count }} donor{{ campaign.donor_count === 1 ? '' : 's' }}
                    <span v-if="daysLeft !== null"> · {{ daysLeft }} days left</span>
                </p>
            </div>

            <span class="mt-4 inline-flex rounded-lg bg-maroon-600 px-4 py-2 text-sm font-semibold text-white">
                Donate now
            </span>
        </div>
    </Link>
</template>
