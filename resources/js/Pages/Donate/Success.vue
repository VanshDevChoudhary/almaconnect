<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { formatINR } from '@/lib/format';
import { prefersReducedMotion } from '@/composables/useAuthAnimation';

const props = defineProps({
    donation: { type: Object, required: true },
});

const check = ref(null);
const card = ref(null);

onMounted(() => {
    if (prefersReducedMotion()) return;
    if (card.value) {
        gsap.from(card.value, { opacity: 0, scale: 0.95, duration: 0.25, ease: 'power2.out' });
    }
    if (check.value) {
        const path = check.value.querySelector('path');
        if (path) {
            const len = path.getTotalLength();
            gsap.fromTo(
                path,
                { strokeDasharray: len, strokeDashoffset: len },
                { strokeDashoffset: 0, duration: 0.6, ease: 'power2.out', delay: 0.15 },
            );
        }
        gsap.from(check.value, { scale: 0, duration: 0.5, ease: 'back.out(2)' });
    }
});
</script>

<template>
    <Head title="Thank you" />

    <AuthenticatedLayout>
        <div class="flex min-h-[70vh] items-center justify-center px-4 py-12">
            <div ref="card" class="w-full max-w-md rounded-xl border border-gray-200 bg-white p-8 text-center shadow-sm">
                <div ref="check" class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-50">
                    <svg class="h-9 w-9 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                </div>

                <h1 class="mt-6 text-xl font-bold text-gray-900">
                    Thank you, {{ donation.donor_name }}!
                </h1>
                <p class="mt-2 text-sm text-gray-600">
                    Your contribution of
                    <span class="font-semibold">{{ formatINR(donation.amount) }}</span>
                    to {{ donation.campaign_title }} has been received.
                </p>
                <p class="mt-2 text-sm text-gray-500">
                    A receipt has been emailed to {{ donation.email }}.
                </p>

                <a
                    v-if="donation.status === 'success'"
                    :href="route('donate.receipt', donation.id)"
                    class="mt-6 inline-flex w-full items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700"
                >
                    Download receipt PDF
                </a>

                <div class="mt-3 flex gap-2">
                    <Link :href="route('donate.index')" class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Donate again
                    </Link>
                    <Link :href="route('dashboard')" class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Back to home
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
