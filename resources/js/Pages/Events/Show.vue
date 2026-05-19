<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import RSVPCard from '@/Components/RSVPCard.vue';
import AttendeesGrid from '@/Components/AttendeesGrid.vue';
import { renderMarkdown } from '@/lib/markdown';
import { useToast } from '@/Composables/useToast';
import { useConfirm } from '@/Composables/useConfirm';

const props = defineProps({
    event: { type: Object, required: true },
    userStatus: { type: String, default: null },
    attendees: { type: Array, default: () => [] },
    attendeesTotal: { type: Number, default: 0 },
    isAdmin: { type: Boolean, default: false },
});

const { showToast } = useToast();
const { confirm } = useConfirm();

const start = computed(() => dayjs(props.event.starts_at));
const end = computed(() => (props.event.ends_at ? dayjs(props.event.ends_at) : null));
const whenLabel = computed(() => {
    let s = start.value.format('dddd, MMMM D · h:mm A');
    if (end.value) s += ` – ${end.value.format('h:mm A')}`;
    return s;
});
const rendered = computed(() => renderMarkdown(props.event.description));
const rsvpTotal = computed(() => props.event.going_count + props.event.interested_count);

async function destroy() {
    const n = rsvpTotal.value;
    const ok = await confirm({
        title: 'Delete this event?',
        body: n > 0
            ? `This event has ${n} RSVP${n === 1 ? '' : 's'}. Deleting removes the event and all RSVPs. This can't be undone.`
            : "This can't be undone.",
        confirmLabel: 'Delete',
    });
    if (!ok) return;
    router.delete(route('admin.events.destroy', props.event.slug), {
        onSuccess: () => showToast('Event deleted.'),
    });
}
</script>

<template>
    <Head :title="event.title" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <Link :href="route('events.index')" class="text-sm text-gray-500 hover:text-gray-700">
                        ← Back to events
                    </Link>
                    <div v-if="isAdmin" class="flex gap-2">
                        <Link
                            :href="route('admin.events.edit', event.slug)"
                            class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Edit
                        </Link>
                        <button
                            type="button"
                            class="rounded-lg border border-red-300 px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50"
                            @click="destroy"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <div
                    v-if="event.cover_image"
                    class="mt-4 aspect-[16/9] w-full overflow-hidden rounded-xl"
                >
                    <img :src="`/storage/${event.cover_image}`" class="h-full w-full object-cover" :alt="event.title" />
                </div>

                <div class="mt-6 grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <h1 class="text-2xl font-bold text-gray-900">{{ event.title }}</h1>
                        <p class="mt-2 text-sm text-gray-600">📅 {{ whenLabel }}</p>
                        <p v-if="event.location" class="text-sm text-gray-600">📍 {{ event.location }}</p>

                        <div class="prose prose-sm mt-6 max-w-none text-gray-800">
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                                About this event
                            </h2>
                            <div class="mt-2" v-html="rendered"></div>
                        </div>

                        <div class="mt-8">
                            <AttendeesGrid :attendees="attendees" :total="attendeesTotal" />
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <RSVPCard
                            :event="event"
                            :slug="event.slug"
                            :initial-status="userStatus"
                            :going-count="event.going_count"
                            :interested-count="event.interested_count"
                            :capacity="event.capacity"
                            :is-past="event.is_past"
                        />
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
