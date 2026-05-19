<script setup>
import { ref, computed, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import Toast from '@/Components/Toast.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { useToast } from '@/Composables/useToast';

const page = usePage();
const user = computed(() => page.props.auth.user);
const counts = computed(() => page.props.adminCounts ?? {});
const sidebarOpen = ref(false);
const { showToast } = useToast();

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) showToast(flash.success, 'success');
        if (flash?.error) showToast(flash.error, 'error');
        if (flash?.info) showToast(flash.info, 'info');
        if (flash?.warning) showToast(flash.warning, 'warning');
    },
);

const navItems = [
    { label: 'Dashboard', route: 'admin.index', icon: '📊' },
    { label: 'Verification', route: 'admin.verification.index', icon: '✅', badge: 'pending_verification' },
    { label: 'Users', route: 'admin.users.index', icon: '👥' },
    { label: 'Events', route: 'admin.events.index', icon: '📅' },
    { label: 'Campaigns', route: 'admin.campaigns.index', icon: '💰' },
    { label: 'Donations', route: 'admin.donations.index', icon: '💳' },
    { label: 'Stories', route: 'admin.stories.index', icon: '✨', badge: 'pending_stories' },
    { label: 'Surveys', route: 'admin.surveys.index', icon: '📋' },
    { label: 'Feedback', route: 'admin.feedback.index', icon: '💬', badge: 'unresolved_feedback' },
    { label: 'Jobs', route: 'admin.jobs.index', icon: '💼' },
    { label: 'Roster', route: 'admin.roster.index', icon: '📋' },
    { label: 'Settings', route: 'admin.settings.index', icon: '⚙️' },
];

function isActive(routeName) {
    try {
        return route().current(routeName);
    } catch {
        return false;
    }
}
</script>

<template>
    <div class="flex min-h-screen bg-gray-50">
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-30 bg-black/40 md:hidden"
            @click="sidebarOpen = false"
        ></div>

        <aside
            :class="[
                'fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-gray-200 bg-white transition-transform md:translate-x-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <div class="flex h-16 items-center gap-2 border-b border-gray-200 px-5">
                <Link href="/" class="text-lg font-bold text-gray-900">
                    Alma<span class="text-indigo-600">Connect</span>
                </Link>
                <span class="rounded-md bg-indigo-100 px-1.5 py-0.5 text-xs font-semibold text-indigo-700">Admin</span>
            </div>

            <nav class="flex-1 overflow-y-auto py-3">
                <template v-for="item in navItems" :key="item.route">
                    <Link
                        :href="route(item.route)"
                        :class="[
                            'flex items-center justify-between px-4 py-2 text-sm font-medium transition-colors',
                            isActive(item.route)
                                ? 'bg-indigo-50 text-indigo-700'
                                : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900',
                        ]"
                        @click="sidebarOpen = false"
                    >
                        <span class="flex items-center gap-2.5">
                            <span class="text-base">{{ item.icon }}</span>
                            {{ item.label }}
                        </span>
                        <span
                            v-if="item.badge && counts[item.badge] > 0"
                            class="rounded-full bg-red-100 px-1.5 py-0.5 text-xs font-semibold text-red-700"
                        >
                            {{ counts[item.badge] }}
                        </span>
                    </Link>
                </template>
            </nav>
        </aside>

        <div class="flex flex-1 flex-col md:ml-64">
            <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 sm:px-6">
                <button
                    type="button"
                    class="rounded-md p-2 text-gray-500 hover:text-gray-900 md:hidden"
                    @click="sidebarOpen = true"
                >
                    ☰
                </button>

                <div class="ml-auto">
                    <Dropdown align="right">
                        <template #trigger>
                            <button type="button" class="flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-100">
                                {{ user.name }}
                                <svg class="h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('dashboard')">View site</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">Log out</DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <main class="flex-1 p-6 sm:p-8">
                <div v-if="$slots.header" class="mb-6">
                    <slot name="header" />
                </div>
                <slot />
            </main>
        </div>

        <Toast />
        <ConfirmModal />
    </div>
</template>
