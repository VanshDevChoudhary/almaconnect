<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const currentYear = new Date().getFullYear();
const years = computed(() => {
    const list = [];
    for (let y = currentYear + 5; y >= 1980; y--) list.push(y);
    return list;
});
const branches = ['CSE', 'ECE', 'ME', 'CE', 'EE', 'IT', 'Chemical', 'Civil', 'Other'];

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'alumni',
    batch: currentYear,
    branch: 'CSE',
    roll_no: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div data-auth-field class="mb-6 text-center">
            <h1 class="text-xl font-semibold text-gray-900">
                Join the alumni network
            </h1>
            <p class="mt-1 text-sm text-gray-600">
                Create your AlmaConnect account.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div data-auth-field>
                <InputLabel for="name" value="Full name" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div data-auth-field>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div data-auth-field>
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div data-auth-field>
                <InputLabel
                    for="password_confirmation"
                    value="Confirm password"
                />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div data-auth-field>
                <InputLabel value="I am a" />
                <div class="mt-2 grid grid-cols-2 gap-3">
                    <label
                        :class="[
                            'cursor-pointer rounded-lg border p-4 text-center text-sm font-medium transition',
                            form.role === 'alumni'
                                ? 'border-maroon-600 bg-maroon-50 text-maroon-700 ring-1 ring-maroon-600'
                                : 'border-gray-300 text-gray-700 hover:bg-gray-50',
                        ]"
                    >
                        <input
                            type="radio"
                            value="alumni"
                            v-model="form.role"
                            class="sr-only"
                        />
                        Alumnus
                    </label>
                    <label
                        :class="[
                            'cursor-pointer rounded-lg border p-4 text-center text-sm font-medium transition',
                            form.role === 'student'
                                ? 'border-maroon-600 bg-maroon-50 text-maroon-700 ring-1 ring-maroon-600'
                                : 'border-gray-300 text-gray-700 hover:bg-gray-50',
                        ]"
                    >
                        <input
                            type="radio"
                            value="student"
                            v-model="form.role"
                            class="sr-only"
                        />
                        Student
                    </label>
                </div>
                <InputError class="mt-2" :message="form.errors.role" />
            </div>

            <div data-auth-field class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="batch" value="Graduation year" />
                    <select
                        id="batch"
                        v-model="form.batch"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-maroon-500 focus:ring-maroon-500"
                    >
                        <option v-for="y in years" :key="y" :value="y">
                            {{ y }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.batch" />
                </div>
                <div>
                    <InputLabel for="branch" value="Branch" />
                    <select
                        id="branch"
                        v-model="form.branch"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-maroon-500 focus:ring-maroon-500"
                    >
                        <option v-for="b in branches" :key="b" :value="b">
                            {{ b }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.branch" />
                </div>
            </div>

            <div data-auth-field>
                <InputLabel for="roll_no" value="Roll number (optional)" />
                <TextInput
                    id="roll_no"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.roll_no"
                    autocomplete="off"
                />
                <InputError class="mt-2" :message="form.errors.roll_no" />
            </div>

            <div data-auth-field>
                <label class="flex items-start gap-2">
                    <input
                        type="checkbox"
                        v-model="form.terms"
                        class="mt-0.5 rounded border-gray-300 text-maroon-600 shadow-sm focus:ring-maroon-500"
                    />
                    <span class="text-sm text-gray-600">
                        I agree to the
                        <a href="/terms" class="text-maroon-600 hover:text-maroon-700">terms</a>
                        and
                        <a href="/privacy" class="text-maroon-600 hover:text-maroon-700">privacy policy</a>.
                    </span>
                </label>
                <InputError class="mt-2" :message="form.errors.terms" />
            </div>

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
                {{ form.processing ? 'Creating account…' : 'Create account' }}
            </button>

            <p data-auth-field class="text-center text-sm text-gray-600">
                Already have an account?
                <Link
                    :href="route('login')"
                    class="font-medium text-maroon-600 hover:text-maroon-700"
                >
                    Log in
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
