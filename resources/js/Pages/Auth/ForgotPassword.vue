<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <div data-auth-field class="mb-6 text-center">
            <h1 class="text-xl font-semibold text-gray-900">
                Forgot your password?
            </h1>
            <p class="mt-1 text-sm text-gray-600">
                Enter your email and we'll send you a reset link.
            </p>
        </div>

        <div
            v-if="status"
            class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div data-auth-field>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <button
                data-auth-field
                type="submit"
                :class="{ 'opacity-60': form.processing }"
                :disabled="form.processing"
                class="flex w-full items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition active:scale-[0.98] hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
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
                {{ form.processing ? 'Sending…' : 'Email password reset link' }}
            </button>

            <p data-auth-field class="text-center text-sm text-gray-600">
                <Link
                    :href="route('login')"
                    class="font-medium text-indigo-600 hover:text-indigo-700"
                >
                    Back to login
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
