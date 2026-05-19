<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <div data-auth-field class="mb-6 text-center">
            <h1 class="text-xl font-semibold text-gray-900">
                Verify your email
            </h1>
            <p class="mt-1 text-sm leading-6 text-gray-600">
                Thanks for signing up! Click the link we just emailed you to
                verify your address. Didn't get it? We'll send another.
            </p>
        </div>

        <div
            v-if="verificationLinkSent"
            class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
        >
            A new verification link has been sent to your email address.
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <button
                data-auth-field
                type="submit"
                :class="{ 'opacity-60': form.processing }"
                :disabled="form.processing"
                class="flex w-full items-center justify-center rounded-lg bg-maroon-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition active:scale-[0.98] hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:ring-offset-2"
            >
                <svg
                    v-if="form.processing"
                    class="mr-2 h-4 w-4 animate-spin"
                    viewBox="0 0 24 24"
                    fill="none"
                >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z" />
                </svg>
                {{ form.processing ? 'Sending…' : 'Resend verification email' }}
            </button>

            <Link
                data-auth-field
                :href="route('logout')"
                method="post"
                as="button"
                class="block w-full rounded-lg px-4 py-2.5 text-center text-sm font-medium text-gray-500 transition hover:text-gray-900"
            >
                Log out
            </Link>
        </form>
    </GuestLayout>
</template>
