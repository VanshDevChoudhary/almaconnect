<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import dayjs from 'dayjs';

const props = defineProps({
    events: { type: Array, default: () => [] },
});

const cursor = ref(dayjs().startOf('month'));
const selectedDay = ref(null);

const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const monthLabel = computed(() => cursor.value.format('MMMM YYYY'));

const cells = computed(() => {
    const start = cursor.value.startOf('month');
    const leading = start.day();
    const daysInMonth = cursor.value.daysInMonth();
    const list = [];
    for (let i = 0; i < leading; i++) list.push(null);
    for (let d = 1; d <= daysInMonth; d++) {
        const date = start.date(d);
        const dayEvents = props.events.filter((e) =>
            dayjs(e.starts_at).isSame(date, 'day'),
        );
        list.push({ date, day: d, events: dayEvents });
    }
    return list;
});

const selectedEvents = computed(() => {
    if (!selectedDay.value) return [];
    return props.events.filter((e) =>
        dayjs(e.starts_at).isSame(selectedDay.value, 'day'),
    );
});

function move(delta) {
    cursor.value = cursor.value.add(delta, 'month');
    selectedDay.value = null;
}

function pick(cell) {
    if (cell?.events.length) selectedDay.value = cell.date;
}

function open(slug) {
    router.visit(route('events.show', slug));
}
</script>

<template>
    <div>
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">{{ monthLabel }}</h2>
            <div class="flex gap-1">
                <button
                    type="button"
                    class="rounded-md border border-gray-300 px-3 py-1 text-sm hover:bg-gray-50"
                    @click="move(-1)"
                >
                    ←
                </button>
                <button
                    type="button"
                    class="rounded-md border border-gray-300 px-3 py-1 text-sm hover:bg-gray-50"
                    @click="move(1)"
                >
                    →
                </button>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-7 gap-px overflow-hidden rounded-lg border border-gray-200 bg-gray-200 text-center text-xs">
            <div
                v-for="w in weekdays"
                :key="w"
                class="bg-gray-50 py-2 font-semibold text-gray-500"
            >
                {{ w }}
            </div>
            <div
                v-for="(cell, i) in cells"
                :key="i"
                class="min-h-[84px] bg-white p-1.5 text-left transition"
                :class="cell?.events.length ? 'cursor-pointer hover:bg-indigo-50' : ''"
                @click="pick(cell)"
            >
                <template v-if="cell">
                    <span
                        class="text-xs font-medium"
                        :class="cell.date.isSame(dayjs(), 'day') ? 'rounded-full bg-indigo-600 px-1.5 py-0.5 text-white' : 'text-gray-700'"
                    >
                        {{ cell.day }}
                    </span>
                    <div class="mt-1 space-y-0.5">
                        <button
                            v-for="ev in cell.events.slice(0, 3)"
                            :key="ev.slug"
                            type="button"
                            class="block w-full truncate rounded bg-indigo-100 px-1 py-0.5 text-left text-[10px] text-indigo-700 hover:bg-indigo-200"
                            :title="ev.title"
                            @click.stop="open(ev.slug)"
                        >
                            {{ ev.title }}
                        </button>
                        <span
                            v-if="cell.events.length > 3"
                            class="text-[10px] text-gray-400"
                        >
                            +{{ cell.events.length - 3 }} more
                        </span>
                    </div>
                </template>
            </div>
        </div>

        <div v-if="selectedDay" class="mt-4 rounded-lg border border-gray-200 bg-white p-4">
            <h3 class="text-sm font-semibold text-gray-900">
                {{ selectedDay.format('dddd, MMMM D') }}
            </h3>
            <ul class="mt-2 space-y-1">
                <li v-for="ev in selectedEvents" :key="ev.slug">
                    <button
                        type="button"
                        class="text-sm text-indigo-600 hover:text-indigo-700"
                        @click="open(ev.slug)"
                    >
                        {{ ev.title }} · {{ dayjs(ev.starts_at).format('h:mm A') }}
                    </button>
                </li>
            </ul>
        </div>
    </div>
</template>
