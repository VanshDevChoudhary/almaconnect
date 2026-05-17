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
                <p v-else class="mt-16 text-center text-sm text-gray-500">
                    No groups match your filters.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
