<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useAuthAnimation } from '@/composables/useAuthAnimation';

const card = ref(null);
useAuthAnimation(card);
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gradient-to-br from-gray-50 via-white to-maroon-50 px-4 py-12 sm:justify-center"
    >
        <Link
            href="/"
            class="mb-8 text-2xl font-semibold tracking-tight text-gray-900"
        >
            Alma<span class="text-maroon-600">Connect</span>
        </Link>

        <div
            ref="card"
            class="w-full max-w-md rounded-xl border border-gray-200 bg-white p-8 shadow-xl"
        >
            <slot />
        </div>
    </div>
</template>
