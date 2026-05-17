<script setup>
import { ref, computed, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    pendingSurveys: { type: Array, default: () => [] },
});

const STORAGE_KEY = 'dismissed_survey_ids';

function getDismissed() {
    try {
        return JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '[]');
    } catch {
        return [];
    }
}

const dismissedIds = ref(getDismissed());

const visibleSurvey = computed(() =>
    props.pendingSurveys.find((s) => !dismissedIds.value.includes(s.id)) ?? null,
);

function dismiss() {
    if (visibleSurvey.value) {
        dismissedIds.value = [...dismissedIds.value, visibleSurvey.value.id];
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify(dismissedIds.value));
    }
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <Transition
                    enter-active-class="transition duration-350 ease-out"
                    enter-from-class="-translate-y-3 opacity-0"
                    enter-to-class="translate-y-0 opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="visibleSurvey"
                        class="mb-6 flex items-center justify-between rounded-xl bg-indigo-50 px-5 py-4 shadow-sm"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-indigo-600">📋</span>
                            <p class="text-sm font-medium text-indigo-900">
                                A new survey is open:
                                <span class="font-semibold">{{ visibleSurvey.title }}</span>
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <Link
                                :href="route('surveys.show', visibleSurvey.id)"
                                class="text-sm font-semibold text-indigo-700 hover:text-indigo-900"
                            >
                                Take it →
                            </Link>
                            <button
                                type="button"
                                class="text-indigo-400 hover:text-indigo-700"
                                aria-label="Dismiss"
                                @click="dismiss"
                            >
                                &times;
                            </button>
                        </div>
                    </div>
                </Transition>

                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <p class="text-gray-700">Welcome back! Use the navigation to explore the platform.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
