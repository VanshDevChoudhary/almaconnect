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
                        <p v-if="!upcoming.length" class="py-16 text-center text-sm text-gray-500">
                            No upcoming events. Check back soon.
                        </p>
                    </div>

                    <div v-else-if="activeTab === 'past'" class="space-y-4">
                        <EventCard
                            v-for="e in past"
                            :key="e.slug"
                            :event="e"
                            data-event
                        />
                        <p v-if="!past.length" class="py-16 text-center text-sm text-gray-500">
                            No past events yet.
                        </p>
                    </div>

                    <EventCalendar v-else :events="allEvents" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
