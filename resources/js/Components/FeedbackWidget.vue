<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';

const page = usePage();
const { showToast } = useToast();

const user = page.props.auth?.user ?? null;
const visible = ref(false);
const open = ref(false);
const processing = ref(false);
const errors = ref({});

const form = ref({
    category: 'suggestion',
    name: user?.name ?? '',
    email: user?.email ?? '',
    message: '',
});

onMounted(() => {
    setTimeout(() => (visible.value = true), 1500);
});

function reset() {
    form.value = {
        category: 'suggestion',
        name: user?.name ?? '',
        email: user?.email ?? '',
        message: '',
    };
    errors.value = {};
}

async function submit() {
    if (processing.value) return;
    processing.value = true;
    errors.value = {};
    try {
        const { data } = await window.axios.post(route('feedback.store'), form.value);
        showToast(data.message || 'Thanks for your feedback.');
        open.value = false;
        reset();
    } catch (e) {
        if (e?.response?.status === 422) {
            errors.value = e.response.data.errors || {};
        } else if (e?.response?.status === 429) {
            showToast('Too many submissions. Please try again later.', 'error');
        } else {
            showToast('Could not send feedback.', 'error');
        }
    } finally {
        processing.value = false;
    }
}

defineExpose({ openModal: () => (open.value = true) });
</script>

<template>
    <div>
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-12 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
        >
            <button
                v-if="visible && !open"
                type="button"
                class="fixed bottom-5 right-5 z-40 rounded-full bg-maroon-600 px-4 py-3 text-sm font-semibold text-white shadow-lg transition hover:bg-maroon-700"
                title="Send feedback"
                @click="open = true"
            >
                💬 Feedback
            </button>
        </Transition>

        <Transition
            enter-active-class="transition duration-250 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
                @click.self="open = false"
            >
                <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                    <div class="flex items-start justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Send feedback</h2>
                        <button type="button" class="text-gray-400 hover:text-gray-700" @click="open = false">&times;</button>
                    </div>

                    <form class="mt-4 space-y-4" @submit.prevent="submit">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Category</p>
                            <div class="mt-2 flex gap-2">
                                <label
                                    v-for="c in [
                                        { v: 'bug', l: 'Bug' },
                                        { v: 'suggestion', l: 'Suggestion' },
                                        { v: 'general', l: 'General' },
                                    ]"
                                    :key="c.v"
                                    :class="[
                                        'cursor-pointer rounded-lg border px-3 py-1.5 text-sm font-medium transition',
                                        form.category === c.v
                                            ? 'border-maroon-600 bg-maroon-50 text-maroon-700'
                                            : 'border-gray-300 text-gray-700 hover:bg-gray-50',
                                    ]"
                                >
                                    <input type="radio" :value="c.v" v-model="form.category" class="sr-only" />
                                    {{ c.l }}
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-maroon-500 focus:ring-maroon-500" />
                            <p v-if="errors.name" class="mt-1 text-xs text-red-600">{{ errors.name[0] }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input v-model="form.email" type="email" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-maroon-500 focus:ring-maroon-500" />
                            <p v-if="errors.email" class="mt-1 text-xs text-red-600">{{ errors.email[0] }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Message</label>
                            <textarea v-model="form.message" rows="4" maxlength="5000" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-maroon-500 focus:ring-maroon-500"></textarea>
                            <div class="mt-1 flex justify-between text-xs text-gray-400">
                                <span v-if="errors.message" class="text-red-600">{{ errors.message[0] }}</span>
                                <span>{{ form.message.length }} / 5000</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="submit" :disabled="processing" class="rounded-lg bg-maroon-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-maroon-700 disabled:opacity-60">
                                {{ processing ? 'Sending…' : 'Send feedback' }}
                            </button>
                            <button type="button" class="text-sm text-gray-500 hover:text-gray-900" @click="open = false">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </div>
</template>
