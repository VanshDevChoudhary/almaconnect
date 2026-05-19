<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import dayjs from 'dayjs';

const props = defineProps({
    event: { type: Object, required: true },
});

const start = computed(() => dayjs(props.event.starts_at));
const month = computed(() => start.value.format('MMM').toUpperCase());
const day = computed(() => start.value.format('D'));
const whenLabel = computed(() =>
    start.value.format('dddd, MMMM D · h:mm A'),
);
</script>

<template>
    <Link
        :href="route('events.show', event.slug)"
        class="flex gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md"
    >
        <div class="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-lg bg-maroon-50 text-maroon-700">
            <span class="text-xs font-semibold">{{ month }}</span>
            <span class="text-2xl font-bold leading-none">{{ day }}</span>
        </div>

        <div class="min-w-0 flex-1">
            <h3 class="font-semibold text-gray-900">{{ event.title }}</h3>
            <p class="mt-0.5 text-sm text-gray-500">{{ whenLabel }}</p>
            <p v-if="event.location" class="text-sm text-gray-500">📍 {{ event.location }}</p>
            <p v-else-if="event.online_url" class="text-sm text-gray-500">💻 Online</p>

            <div class="mt-3 flex items-center gap-3 text-sm">
                <span class="text-gray-600">{{ event.going_count }} going</span>
                <span class="text-gray-400">·</span>
                <span class="text-gray-600">{{ event.interested_count }} interested</span>
                <span
                    v-if="event.user_status"
                    class="ml-auto rounded-full px-2.5 py-0.5 text-xs font-medium"
                    :class="event.user_status === 'going'
                        ? 'bg-green-50 text-green-700'
                        : event.user_status === 'interested'
                          ? 'bg-amber-50 text-amber-700'
                          : 'bg-gray-100 text-gray-500'"
                >
                    {{ event.user_status === 'going' ? 'Going ✓' : event.user_status === 'interested' ? 'Interested' : 'Not going' }}
                </span>
            </div>
        </div>
    </Link>
</template>
