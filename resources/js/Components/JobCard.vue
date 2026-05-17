<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { formatSalary, JOB_TYPE_LABELS } from '@/lib/format';

dayjs.extend(relativeTime);

const props = defineProps({
    job: { type: Object, required: true },
});

const salary = computed(() =>
    formatSalary(props.job.salary_min, props.job.salary_max, props.job.salary_currency),
);
const preview = computed(() =>
    String(props.job.description || '').replace(/[#*_`>\[\]]/g, '').slice(0, 180),
);
const posterMeta = computed(() => {
    const p = props.job.poster;
    if (!p) return '';
    const bits = [p.branch, p.batch].filter(Boolean).join(" '");
    return bits ? `${p.name} (${bits})` : p.name;
});
</script>

<template>
    <Link
        :href="route('jobs.show', job.id)"
        class="block rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md"
    >
        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-indigo-600">
            {{ job.title }}
        </h3>
        <p class="mt-0.5 text-sm text-gray-600">
            {{ job.company }}<span v-if="job.location"> · {{ job.location }}</span>
        </p>

        <div class="mt-3 flex flex-wrap items-center gap-2 text-sm">
            <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">
                {{ JOB_TYPE_LABELS[job.type] || job.type }}
            </span>
            <span v-if="salary" class="font-medium text-gray-700">{{ salary }}</span>
        </div>

        <p class="mt-3 line-clamp-2 text-sm text-gray-600">{{ preview }}</p>

        <div v-if="job.skills.length" class="mt-3 flex flex-wrap gap-1.5">
            <span
                v-for="s in job.skills.slice(0, 3)"
                :key="s"
                class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-700"
            >
                {{ s }}
            </span>
        </div>

        <p class="mt-4 text-xs text-gray-500">
            Posted {{ dayjs(job.created_at).fromNow() }}
            <span v-if="posterMeta">by {{ posterMeta }}</span>
        </p>
    </Link>
</template>
