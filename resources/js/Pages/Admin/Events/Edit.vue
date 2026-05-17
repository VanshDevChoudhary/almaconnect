<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EventForm from '@/Components/EventForm.vue';

defineProps({
    event: { type: Object, required: true },
});
</script>

<template>
    <Head title="Edit event" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Edit event
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <Link :href="route('events.show', event.slug)" class="text-sm text-gray-500 hover:text-gray-700">
                    ← Back to event
                </Link>
                <div class="mt-4 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <EventForm mode="edit" :event="event" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
