<script setup>
import UserAvatar from '@/Components/UserAvatar.vue';

defineProps({
    attendees: { type: Array, default: () => [] },
    total: { type: Number, default: 0 },
});
</script>

<template>
    <div>
        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
            Attendees ({{ total }})
        </h2>
        <div v-if="attendees.length" class="mt-3 flex flex-wrap items-center gap-3">
            <div
                v-for="a in attendees"
                :key="a.id"
                class="flex flex-col items-center gap-1"
                :title="a.name"
            >
                <UserAvatar :user="a" size="md" />
                <span class="max-w-[4.5rem] truncate text-xs text-gray-600">{{ a.name }}</span>
            </div>
            <span
                v-if="total > attendees.length"
                class="text-sm text-gray-500"
            >
                +{{ total - attendees.length }} more
            </span>
        </div>
        <p v-else class="mt-2 text-sm text-gray-400">No one is going yet.</p>
    </div>
</template>
