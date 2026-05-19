<script setup>
import { ref, reactive, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SearchBar from '@/Components/SearchBar.vue';
import JobFilters from '@/Components/JobFilters.vue';
import JobCard from '@/Components/JobCard.vue';
import DirectoryPagination from '@/Components/DirectoryPagination.vue';
import { prefersReducedMotion } from '@/composables/useAuthAnimation';

const props = defineProps({
    jobs: { type: Object, required: true },
    filters: { type: Object, default: () => ({ type: [], location: '', q: '', sort: 'latest' }) },
    canPost: { type: Boolean, default: false },
});

const q = ref(props.filters.q || '');
const state = reactive({
    type: [...(props.filters.type || [])],
    location: props.filters.location || '',
    sort: props.filters.sort || 'latest',
});

const list = ref(null);
let locTimer = null;

function navigate() {
    router.get(
        route('jobs.index'),
        {
            q: q.value || undefined,
            type: state.type.length ? state.type : undefined,
            location: state.location || undefined,
            sort: state.sort !== 'latest' ? state.sort : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function toggleType(t) {
    const i = state.type.indexOf(t);
    if (i === -1) state.type.push(t);
    else state.type.splice(i, 1);
    navigate();
}

function setLocation(v) {
    state.location = v;
    clearTimeout(locTimer);
    locTimer = setTimeout(navigate, 350);
}

function clearFilters() {
    state.type = [];
    state.location = '';
    q.value = '';
    navigate();
}

function animate() {
    if (!list.value || prefersReducedMotion()) return;
    gsap.from(list.value.querySelectorAll('[data-job]'), {
        opacity: 0, y: 16, duration: 0.25, stagger: 0.06, ease: 'power2.out',
    });
}

let stop;
onMounted(() => {
    animate();
    stop = router.on('finish', () => nextTick(animate));
});
onUnmounted(() => stop?.());
</script>

<template>
    <Head title="Jobs" />

    <AuthenticatedLayout>
        <div class="py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Jobs</h1>
                        <p class="mt-1 text-sm text-gray-600">Opportunities shared by our alumni.</p>
                    </div>
                    <div class="flex gap-2">
                        <Link :href="route('jobs.mine')" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            My posts
                        </Link>
                        <Link
                            v-if="canPost"
                            :href="route('jobs.create')"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                        >
                            + Post a job
                        </Link>
                    </div>
                </div>

                <div class="mt-6 flex gap-8">
                    <aside class="hidden w-64 shrink-0 md:block">
                        <div class="sticky top-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <JobFilters
                                :selected-types="state.type"
                                :location="state.location"
                                @toggle-type="toggleType"
                                @update:location="setLocation"
                                @clear="clearFilters"
                            />
                        </div>
                    </aside>

                    <div class="min-w-0 flex-1">
                        <SearchBar v-model="q" @search="navigate" />

                        <div class="mt-4 flex items-center justify-between">
                            <p class="text-sm text-gray-600">
                                Showing {{ jobs.from || 0 }}–{{ jobs.to || 0 }} of {{ jobs.total }} active jobs
                            </p>
                            <select
                                v-model="state.sort"
                                class="rounded-md border-gray-300 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                @change="navigate"
                            >
                                <option value="latest">Latest</option>
                                <option value="salary_high">Salary: high to low</option>
                                <option value="salary_low">Salary: low to high</option>
                            </select>
                        </div>

                        <div ref="list" class="mt-4 space-y-4">
                            <JobCard v-for="j in jobs.data" :key="j.id" :job="j" data-job />
                            <div v-if="!jobs.data.length" class="py-16 text-center">
                                <svg class="mx-auto h-14 w-14 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" />
                                </svg>
                                <h3 class="mt-3 text-base font-semibold text-gray-900">No active jobs right now</h3>
                                <p class="mt-1 text-sm text-gray-500">Check back soon, or post one if you're hiring.</p>
                                <Link v-if="canPost" :href="route('jobs.create')" class="mt-4 inline-block rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                                    Post a job
                                </Link>
                            </div>
                        </div>

                        <div v-if="jobs.total" class="mt-8">
                            <DirectoryPagination :links="jobs.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
