<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    group: { type: Object, required: true },
});

const typeLabels = {
    regional: 'Regional',
    batch: 'Batch',
    interest: 'Interest',
    professional: 'Professional',
};
</script>

<template>
    <Link
        :href="route('groups.show', group.slug)"
        class="group flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md"
    >
        <div
            class="h-28 w-full bg-gradient-to-r from-maroon-500 via-maroon-600 to-maroon-600"
            :style="group.cover_image ? { backgroundImage: `url(/storage/${group.cover_image})`, backgroundSize: 'cover' } : {}"
        ></div>
        <div class="flex flex-1 flex-col p-5">
            <div class="flex items-center gap-2">
                <h3 class="font-semibold text-gray-900">{{ group.name }}</h3>
                <span class="rounded-full bg-maroon-50 px-2 py-0.5 text-xs font-medium text-maroon-700">
                    {{ typeLabels[group.type] || group.type }}
                </span>
            </div>
            <p class="mt-0.5 text-xs text-gray-500">{{ group.members_count }} members</p>
            <p class="mt-2 line-clamp-2 flex-1 text-sm text-gray-600">
                {{ group.description || 'No description yet.' }}
            </p>
            <span
                class="mt-4 inline-flex w-fit items-center rounded-lg px-3 py-1.5 text-sm font-medium"
                :class="group.is_member ? 'bg-green-50 text-green-700' : 'bg-maroon-600 text-white'"
            >
                {{ group.is_member ? 'Member ✓' : 'View & Join' }}
            </span>
        </div>
    </Link>
</template>
