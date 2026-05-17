<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: { type: Array, default: () => [] },
});
</script>

<template>
    <nav v-if="links.length > 3" class="flex flex-wrap items-center justify-center gap-1">
        <template v-for="(link, i) in links" :key="i">
            <span
                v-if="link.url === null"
                class="px-3 py-1.5 text-sm text-gray-400"
                v-html="link.label"
            />
            <Link
                v-else
                :href="link.url"
                preserve-scroll
                preserve-state
                :class="[
                    'rounded-md px-3 py-1.5 text-sm transition',
                    link.active
                        ? 'bg-indigo-600 text-white'
                        : 'text-gray-700 hover:bg-gray-100',
                ]"
                v-html="link.label"
            />
        </template>
    </nav>
</template>
