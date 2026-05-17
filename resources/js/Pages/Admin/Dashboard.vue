<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { prefersReducedMotion } from '@/composables/useAuthAnimation';

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    activity: { type: Array, default: () => [] },
});

const cards = ref(null);

const statsConfig = [
    { key: 'approved_alumni', label: 'Alumni approved', icon: '🎓', link: 'admin.users.index' },
    { key: 'pending_verification', label: 'Pending verification', icon: '⏳', link: 'admin.verification.index' },
    { key: 'donations_this_month', label: 'Donations this month (₹)', icon: '💰', prefix: '₹', link: 'admin.donations.index' },
    { key: 'active_campaigns', label: 'Active campaigns', icon: '🎯', link: 'admin.campaigns.index' },
    { key: 'upcoming_events', label: 'Upcoming events', icon: '📅', link: 'admin.events.index' },
    { key: 'active_jobs', label: 'Active jobs', icon: '💼', link: 'admin.jobs.index' },
];

onMounted(() => {
    if (!cards.value || prefersReducedMotion()) return;
    gsap.from(cards.value.querySelectorAll('[data-stat]'), {
        opacity: 0, y: 14, duration: 0.25, stagger: 0.06, ease: 'power2.out',
    });
});
</script>

<template>
    <Head title="Admin dashboard" />

    <AdminLayout>
        <template #header>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        </template>

        <div ref="cards" class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            <Link
                v-for="s in statsConfig"
                :key="s.key"
                :href="route(s.link)"
                data-stat
                class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:shadow-md"
            >
                <p class="text-2xl">{{ s.icon }}</p>
                <p class="mt-2 text-2xl font-bold text-gray-900">
                    {{ s.prefix ?? '' }}{{ (stats[s.key] ?? 0).toLocaleString() }}
                </p>
                <p class="text-xs text-gray-500">{{ s.label }}</p>
            </Link>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Recent activity</h2>
                <ul class="mt-4 divide-y divide-gray-100">
                    <li v-for="(a, i) in activity" :key="i" class="flex items-center justify-between py-2 text-sm">
                        <span class="text-gray-700">{{ a.label }}</span>
                        <span class="text-xs text-gray-400">{{ a.time }}</span>
                    </li>
                    <li v-if="!activity.length" class="py-8 text-center text-sm text-gray-400">No recent activity.</li>
                </ul>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Quick actions</h2>
                <div class="mt-4 space-y-3">
                    <Link :href="route('admin.events.create')" class="flex w-full items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                        📅 Create event
                    </Link>
                    <Link :href="route('admin.campaigns.create')" class="flex w-full items-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        💰 New campaign
                    </Link>
                    <Link :href="route('admin.roster.index')" class="flex w-full items-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        📤 Upload roster
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
