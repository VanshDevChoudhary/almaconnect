<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SearchBar from '@/Components/SearchBar.vue';
import GroupCard from '@/Components/GroupCard.vue';

const props = defineProps({
    groups: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ type: 'all', q: '' }) },
});

const tabs = [
    { key: 'all', label: 'All' },
    { key: 'regional', label: 'Regional' },
    { key: 'batch', label: 'Batch' },
    { key: 'interest', label: 'Interest' },
    { key: 'professional', label: 'Professional' },
];

const q = ref(props.filters.q || '');
const activeType = ref(props.filters.type || 'all');

function navigate() {
    router.get(
        route('groups.index'),
        { type: activeType.value, q: q.value || undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function setType(t) {
    activeType.value = t;
    navigate();
}
</script>

<template>
    <Head title="Groups" />

    <AuthenticatedLayout>
        <div class="py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold text-gray-900">Groups</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Connect with alumni who share your interests.
                </p>

                <div class="mt-6 flex flex-wrap items-center gap-2">
                    <button
                        v-for="t in tabs"
                        :key="t.key"
                        type="button"
                        :class="[
                            'rounded-full px-4 py-1.5 text-sm font-medium transition',
                            activeType === t.key
                                ? 'bg-indigo-600 text-white'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                        ]"
                        @click="setType(t.key)"
                    >
                        {{ t.label }}
                    </button>
                </div>

                <div class="mt-4 max-w-md">
                    <SearchBar v-model="q" @search="navigate" />
                </div>

                <div
                    v-if="groups.length"
                    class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <GroupCard v-for="g in groups" :key="g.slug" :group="g" />
                </div>
                <div v-else class="mt-16 text-center">
                    <svg class="mx-auto h-14 w-14 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                    <h3 class="mt-3 text-base font-semibold text-gray-900">No groups found</h3>
                    <p class="mt-1 text-sm text-gray-500">Try a different filter or search term.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
