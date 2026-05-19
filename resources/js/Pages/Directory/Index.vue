<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SearchBar from '@/Components/SearchBar.vue';
import DirectoryFilters from '@/Components/DirectoryFilters.vue';
import AlumniCard from '@/Components/AlumniCard.vue';
import DirectoryPagination from '@/Components/DirectoryPagination.vue';
import { prefersReducedMotion } from '@/composables/useAuthAnimation';

const props = defineProps({
    alumni: { type: Object, required: true },
    availableFilters: { type: Object, default: () => ({}) },
    appliedFilters: { type: [Object, Array], default: () => ({}) },
    searchQuery: { type: String, default: '' },
    total: { type: Number, default: 0 },
});

const FACETS = ['batch', 'branch', 'industry', 'city', 'country'];

const q = ref(props.searchQuery);
const filters = reactive({});
FACETS.forEach((f) => {
    const v = props.appliedFilters?.[f];
    filters[f] = Array.isArray(v) ? v.map(String) : [];
});

const appliedCount = computed(() =>
    FACETS.reduce((n, f) => n + filters[f].length, 0),
);

const loading = ref(false);
const hasLoadedOnce = ref(false);
const showFilters = ref(false);
const grid = ref(null);

function buildParams() {
    const params = {};
    if (q.value) params.q = q.value;
    FACETS.forEach((f) => {
        if (filters[f].length) params[f] = filters[f];
    });
    return params;
}

function navigate() {
    router.get(route('directory'), buildParams(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function onSearch(value) {
    q.value = value;
    navigate();
}

function toggleFilter(facet, value) {
    const arr = filters[facet];
    const sv = String(value);
    const i = arr.findIndex((x) => String(x) === sv);
    if (i === -1) arr.push(sv);
    else arr.splice(i, 1);
    navigate();
}

function clearAll() {
    q.value = '';
    FACETS.forEach((f) => (filters[f] = []));
    navigate();
}

const rangeLabel = computed(() => {
    if (!props.total) return '';
    return `Showing ${props.alumni.from}–${props.alumni.to} of ${props.total} alumni`;
});

function animateGrid() {
    if (!grid.value || prefersReducedMotion()) return;
    const cards = grid.value.querySelectorAll('[data-card]');
    gsap.from(cards, {
        opacity: 0,
        y: 16,
        duration: 0.35,
        ease: 'power2.out',
        stagger: 0.05,
    });
}

let stopStart, stopFinish;
onMounted(() => {
    stopStart = router.on('start', () => (loading.value = true));
    stopFinish = router.on('finish', () => {
        loading.value = false;
        hasLoadedOnce.value = true;
        nextTick(animateGrid);
    });
    animateGrid();
});
onUnmounted(() => {
    stopStart?.();
    stopFinish?.();
});
</script>

<template>
    <Head title="Alumni Directory" />

    <AuthenticatedLayout>
        <div class="py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold text-gray-900">Alumni Directory</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Discover and connect with the alumni network.
                </p>

                <div class="mt-6 flex gap-8">
                    <!-- Desktop sidebar -->
                    <aside class="hidden w-72 shrink-0 md:block">
                        <div class="sticky top-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <DirectoryFilters
                                :available-filters="availableFilters"
                                :applied-filters="filters"
                                @toggle="toggleFilter"
                                @clear="clearAll"
                            />
                        </div>
                    </aside>

                    <!-- Main -->
                    <div class="min-w-0 flex-1">
                        <SearchBar v-model="q" @search="onSearch" />

                        <div class="mt-4 flex items-center justify-between">
                            <p class="text-sm text-gray-600">{{ rangeLabel }}</p>
                            <button
                                type="button"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 md:hidden"
                                @click="showFilters = true"
                            >
                                Filters
                                <span v-if="appliedCount" class="text-maroon-600">· {{ appliedCount }}</span>
                            </button>
                        </div>

                        <!-- Skeleton (first load) -->
                        <div
                            v-if="loading && !hasLoadedOnce"
                            class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4"
                        >
                            <div
                                v-for="n in 24"
                                :key="n"
                                class="h-56 animate-pulse rounded-xl border border-gray-200 bg-gray-100"
                            ></div>
                        </div>

                        <!-- Empty state -->
                        <div
                            v-else-if="total === 0"
                            class="mt-16 flex flex-col items-center text-center"
                        >
                            <svg class="h-16 w-16 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                            <h3 class="mt-4 text-lg font-semibold text-gray-900">
                                No alumni match your search
                            </h3>
                            <p class="mt-1 text-sm text-gray-600">
                                Try removing a filter or searching for something else.
                            </p>
                            <button
                                type="button"
                                class="mt-4 rounded-lg bg-maroon-600 px-4 py-2 text-sm font-semibold text-white hover:bg-maroon-700"
                                @click="clearAll"
                            >
                                Clear all filters
                            </button>
                        </div>

                        <!-- Results -->
                        <div
                            v-else
                            ref="grid"
                            :class="[
                                'mt-6 grid grid-cols-1 gap-5 transition-opacity duration-200 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4',
                                loading && hasLoadedOnce ? 'opacity-50' : 'opacity-100',
                            ]"
                        >
                            <AlumniCard
                                v-for="a in alumni.data"
                                :key="a.slug"
                                :alumnus="a"
                                data-card
                            />
                        </div>

                        <div v-if="total > 0" class="mt-8">
                            <DirectoryPagination :links="alumni.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile filter drawer -->
        <Transition
            enter-active-class="transition duration-250 ease-out"
            enter-from-class="translate-y-full"
            enter-to-class="translate-y-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-y-0"
            leave-to-class="translate-y-full"
        >
            <div
                v-if="showFilters"
                class="fixed inset-0 z-50 flex flex-col bg-white md:hidden"
            >
                <div class="flex items-center justify-between border-b border-gray-200 p-4">
                    <h2 class="text-base font-semibold text-gray-900">Filters</h2>
                    <button
                        type="button"
                        class="rounded-md p-1 text-gray-500 hover:text-gray-900"
                        @click="showFilters = false"
                    >
                        &times;
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-5">
                    <DirectoryFilters
                        :available-filters="availableFilters"
                        :applied-filters="filters"
                        @toggle="(f, v) => { toggleFilter(f, v); }"
                        @clear="clearAll"
                    />
                </div>
                <div class="border-t border-gray-200 p-4">
                    <button
                        type="button"
                        class="w-full rounded-lg bg-maroon-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-maroon-700"
                        @click="showFilters = false"
                    >
                        Show {{ total }} results
                    </button>
                </div>
            </div>
        </Transition>
    </AuthenticatedLayout>
</template>
