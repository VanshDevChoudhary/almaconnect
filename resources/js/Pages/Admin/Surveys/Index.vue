<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useToast } from '@/Composables/useToast';

defineProps({ surveys: { type: Array, default: () => [] } });

const { showToast } = useToast();

function destroy(id) {
    if (!confirm('Delete this survey?')) return;
    router.delete(route('admin.surveys.destroy', id), {
        preserveScroll: true,
        onSuccess: () => showToast('Survey deleted.'),
    });
}
</script>

<template>
    <Head title="Surveys" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Surveys</h2>
                <Link :href="route('admin.surveys.create')" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                    + New survey
                </Link>
            </div>
        </template>
        <div class="py-10">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr><th class="px-4 py-3">Title</th><th class="px-4 py-3">Audience</th><th class="px-4 py-3">Qs</th><th class="px-4 py-3">Respondents</th><th class="px-4 py-3">Active</th><th class="px-4 py-3"></th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="s in surveys" :key="s.id">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ s.title }}</td>
                                <td class="px-4 py-3 capitalize text-gray-600">{{ s.target_audience }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ s.question_count }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ s.respondent_count }}</td>
                                <td class="px-4 py-3"><span :class="s.is_active ? 'text-green-700' : 'text-gray-400'">{{ s.is_active ? 'Yes' : 'No' }}</span></td>
                                <td class="px-4 py-3 text-right">
                                    <Link :href="route('admin.surveys.responses', s.id)" class="text-indigo-600 hover:text-indigo-700">Responses</Link>
                                    <Link :href="route('admin.surveys.edit', s.id)" class="ml-3 text-gray-600 hover:text-gray-900">Edit</Link>
                                    <button type="button" class="ml-3 text-red-600 hover:text-red-700" @click="destroy(s.id)">Delete</button>
                                </td>
                            </tr>
                            <tr v-if="!surveys.length"><td colspan="6" class="px-4 py-10 text-center text-gray-500">No surveys yet.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
