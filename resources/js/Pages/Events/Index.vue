<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { Head } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EventCard from '@/Components/EventCard.vue';
import EventCalendar from '@/Components/EventCalendar.vue';
import { prefersReducedMotion } from '@/composables/useAuthAnimation';

const props = defineProps({
    upcoming: { type: Array, default: () => [] },
    past: { type: Array, default: () => [] },
    tab: { type: String, default: 'upcoming' },
});

const activeTab = ref(['upcoming', 'past', 'calendar'].includes(props.tab) ? props.tab : 'upcoming');
const list = ref(null);

const allEvents = computed(() => [...props.upcoming, ...props.past]);

function animate() {
    if (!list.value || prefersReducedMotion()) return;
    gsap.from(list.value.querySelectorAll('[data-event]'), {
        opacity: 0,
        y: 16,
        duration: 0.3,
        stagger: 0.07,
        ease: 'power2.out',
    });
}

onMounted(animate);
watch(activeTab, () => nextTick(animate));
</script>

<template>
    <Head title="Events" />

    <AuthenticatedLayout>
        <div class="py-10">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold text-gray-900">Events</h1>
                <p class="mt-1 text-sm text-gray-600">Find reunions, talks and meetups.</p>

                <div class="mt-6 flex gap-6 border-b border-gray-200">
                    <button
                        v-for="t in [
                            { k: 'upcoming', l: 'Upcoming' },
                            { k: 'past', l: 'Past' },
                            { k: 'calendar', l: '📅 Calendar' },
                        ]"
                        :key="t.k"
                        type="button"
                        :class="[
                            'pb-3 text-sm font-medium transition',
                            activeTab === t.k
                                ? 'border-b-2 border-indigo-600 text-indigo-600'
                                : 'text-gray-500 hover:text-gray-700',
                        ]"
                        @click="activeTab = t.k"
                    >
                        {{ t.l }}
                    </button>
                </div>

                <div ref="list" class="mt-6">
                    <div v-if="activeTab === 'upcoming'" class="space-y-4">
                        <EventCard
                            v-for="e in upcoming"
                            :key="e.slug"
                            :event="e"
                            data-event
                        />
                        <div v-if="!upcoming.length" class="py-16 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                            <h3 class="mt-3 text-sm font-semibold text-gray-900">No upcoming events</h3>
                            <p class="mt-1 text-sm text-gray-500">Check back soon — new events will appear here.</p>
                        </div>
                    </div>

                    <div v-else-if="activeTab === 'past'" class="space-y-4">
                        <EventCard
                            v-for="e in past"
                            :key="e.slug"
                            :event="e"
                            data-event
                        />
                        <div v-if="!past.length" class="py-16 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <h3 class="mt-3 text-sm font-semibold text-gray-900">No past events yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Past events will appear here once they've concluded.</p>
                        </div>
                    </div>

                    <EventCalendar v-else :events="allEvents" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
