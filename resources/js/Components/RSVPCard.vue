<script setup>
import { ref, computed } from 'vue';
import { gsap } from 'gsap';
import { useToast } from '@/Composables/useToast';
import { gcalUrl } from '@/lib/gcal';
import { prefersReducedMotion } from '@/composables/useAuthAnimation';

const props = defineProps({
    event: { type: Object, required: true },
    slug: { type: String, required: true },
    initialStatus: { type: String, default: null },
    goingCount: { type: Number, default: 0 },
    interestedCount: { type: Number, default: 0 },
    capacity: { type: Number, default: null },
    isPast: { type: Boolean, default: false },
});

const { showToast } = useToast();

const status = ref(props.initialStatus);
const going = ref(props.goingCount);
const interested = ref(props.interestedCount);
const busy = ref(false);
const bar = ref(null);

const options = [
    { key: 'going', label: 'Going' },
    { key: 'interested', label: 'Interested' },
    { key: 'not_going', label: 'Not going' },
];

const pct = computed(() =>
    props.capacity ? Math.min(100, Math.round((going.value / props.capacity) * 100)) : 0,
);

const calendarHref = computed(() => gcalUrl(props.event));

async function choose(next) {
    if (busy.value || props.isPast) return;
    busy.value = true;

    const prev = { s: status.value, g: going.value, i: interested.value };

    // Optimistic adjust
    if (status.value === 'going') going.value -= 1;
    if (status.value === 'interested') interested.value -= 1;
    if (next === 'going') going.value += 1;
    if (next === 'interested') interested.value += 1;
    status.value = next;

    try {
        const res = await window.axios.post(route('events.rsvp', props.slug), {
            status: next,
        });
        status.value = res.data.user_status;
        going.value = res.data.going_count;
        interested.value = res.data.interested_count;
        showToast('RSVP saved.');
        if (next === 'going' && bar.value && !prefersReducedMotion()) {
            gsap.fromTo(bar.value, { scaleX: 0.96 }, { scaleX: 1, duration: 0.4, ease: 'power2.out', transformOrigin: 'left' });
        }
    } catch (e) {
        status.value = prev.s;
        going.value = prev.g;
        interested.value = prev.i;
        const msg = e?.response?.status === 422
            ? (e.response.data.message || 'Event is full')
            : 'Could not save your RSVP.';
        showToast(msg, 'error');
    } finally {
        busy.value = false;
    }
}
</script>

<template>
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-gray-900">
            {{ isPast ? 'This event has ended' : 'Are you going?' }}
        </h3>

        <div v-if="!isPast" class="mt-3 space-y-2">
            <button
                v-for="o in options"
                :key="o.key"
                type="button"
                :disabled="busy"
                :class="[
                    'flex w-full items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium transition active:scale-[0.97] disabled:opacity-60',
                    status === o.key
                        ? 'bg-maroon-600 text-white'
                        : 'border border-gray-300 text-gray-700 hover:bg-gray-50',
                ]"
                @click="choose(o.key)"
            >
                <span v-if="status === o.key">✓</span>
                {{ o.label }}
            </button>
        </div>

        <div class="mt-4 text-sm text-gray-600">
            <p>
                <span class="font-semibold text-gray-900">{{ going }}</span>
                {{ capacity ? `of ${capacity}` : '' }} going ·
                <span class="font-semibold text-gray-900">{{ interested }}</span> interested
            </p>
            <div v-if="capacity" class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                <div
                    ref="bar"
                    class="h-full rounded-full bg-maroon-600 transition-[width] duration-500"
                    :style="{ width: pct + '%' }"
                ></div>
            </div>
        </div>

        <a
            :href="calendarHref"
            target="_blank"
            rel="noopener"
            class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
        >
            📅 Add to Google Calendar
        </a>

        <a
            v-if="event.online_url && !isPast"
            :href="event.online_url"
            target="_blank"
            rel="noopener"
            class="mt-2 flex w-full items-center justify-center gap-2 rounded-lg bg-maroon-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-maroon-700"
        >
            Join meeting
        </a>
    </div>
</template>
