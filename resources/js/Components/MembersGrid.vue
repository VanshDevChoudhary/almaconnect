<script setup>
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import UserAvatar from '@/Components/UserAvatar.vue';

dayjs.extend(relativeTime);

defineProps({
    members: { type: Array, default: () => [] },
});
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div
            v-for="m in members"
            :key="m.id"
            class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm"
        >
            <UserAvatar :user="{ id: m.id, name: m.name, avatar: m.avatar }" size="md" />
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-gray-900">
                    {{ m.name }}
                    <span
                        v-if="m.role === 'moderator'"
                        class="ml-1 rounded-full bg-indigo-50 px-1.5 py-0.5 text-xs font-medium text-indigo-700"
                    >
                        mod
                    </span>
                </p>
                <p class="text-xs text-gray-500">
                    Joined {{ m.joined_at ? dayjs(m.joined_at).fromNow() : 'recently' }}
                </p>
            </div>
        </div>
        <p v-if="!members.length" class="text-sm text-gray-500">No members yet.</p>
    </div>
</template>
