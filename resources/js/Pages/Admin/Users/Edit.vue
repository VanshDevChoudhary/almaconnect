<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({ user: { type: Object, required: true } });

const form = useForm({
    role: props.user.role,
    status: props.user.status,
});

function submit() {
    form.patch(route('admin.users.update', props.user.id));
}
</script>

<template>
    <Head title="Edit user" />
    <AdminLayout>
        <template #header><h1 class="text-2xl font-bold text-gray-900">Edit user: {{ user.name }}</h1></template>
        <div class="max-w-sm rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <form class="space-y-5" @submit.prevent="submit">
                <div>
                    <InputLabel for="role" value="Role" />
                    <select id="role" v-model="form.role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="alumni">Alumni</option>
                        <option value="student">Student</option>
                        <option value="admin">Admin</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.role" />
                </div>
                <div>
                    <InputLabel for="status" value="Status" />
                    <select id="status" v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="banned">Banned</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.status" />
                </div>
                <div class="flex gap-2">
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60">Save</button>
                    <Link :href="route('admin.users.index')" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">Cancel</Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
