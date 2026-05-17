<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import UserAvatar from '@/Components/UserAvatar.vue';

const props = defineProps({
    alumnus: { type: Object, required: true },
});

const cardUser = computed(() => ({
    id: props.alumnus.user_id,
    name: props.alumnus.name,
    avatar: props.alumnus.avatar,
}));

const visibleSkills = computed(() => (props.alumnus.skills || []).slice(0, 3));
const extraSkills = computed(() =>
    Math.max(0, (props.alumnus.skills || []).length - 3),
);
</script>

<template>
    <Link
        :href="route('profile.show', alumnus.slug)"
        class="group flex flex-col items-center rounded-xl border border-gray-200 bg-white p-6 text-center shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md"
    >
        <UserAvatar :user="cardUser" size="lg" />

        <div class="mt-3 flex items-center gap-1.5">
            <h3 class="font-semibold text-gray-900">{{ alumnus.name }}</h3>
            <span
                v-if="alumnus.verified"
                title="Verified by the alumni cell"
                class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-amber-500"
            >
                <svg class="h-2.5 w-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
            </span>
        </div>

        <p v-if="alumnus.batch || alumnus.branch" class="mt-0.5 text-sm text-gray-500">
            <template v-if="alumnus.batch">{{ alumnus.batch }}</template>
            <template v-if="alumnus.batch && alumnus.branch"> · </template>
            <template v-if="alumnus.branch">{{ alumnus.branch }}</template>
        </p>

        <p
            v-if="alumnus.current_role || alumnus.current_company"
            class="mt-2 text-sm text-gray-700"
        >
            <span v-if="alumnus.current_role">{{ alumnus.current_role }}</span>
            <span v-if="alumnus.current_role && alumnus.current_company"> @ </span>
            <span v-if="alumnus.current_company" class="font-medium">{{ alumnus.current_company }}</span>
        </p>

        <p v-if="alumnus.city" class="mt-1 text-xs text-gray-500">📍 {{ alumnus.city }}</p>

        <div v-if="visibleSkills.length" class="mt-3 flex flex-wrap justify-center gap-1.5">
            <span
                v-for="skill in visibleSkills"
                :key="skill"
                class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-700"
            >
                {{ skill }}
            </span>
            <span
                v-if="extraSkills"
                class="rounded-full bg-gray-50 px-2.5 py-0.5 text-xs text-gray-500"
            >
                +{{ extraSkills }} more
            </span>
        </div>
    </Link>
</template>
