<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';
import { formatINR } from '@/lib/format';

const props = defineProps({
    campaign: { type: Object, required: true },
    open: { type: Boolean, default: false },
});
const emit = defineEmits(['close']);

const { showToast } = useToast();
const page = usePage();
const user = page.props.auth.user;

const presets = [100, 500, 1000, 5000, 10000];
const step = ref(1);
const amount = ref(500);
const customAmount = ref('');
const isAnonymous = ref(false);
const processing = ref(false);

const finalAmount = computed(() => {
    const c = parseInt(customAmount.value, 10);
    return customAmount.value !== '' && !Number.isNaN(c) ? c : amount.value;
});
const validAmount = computed(() => finalAmount.value >= 100 && finalAmount.value <= 1000000);

function pickPreset(v) {
    amount.value = v;
    customAmount.value = '';
}

function close() {
    step.value = 1;
    emit('close');
}

async function proceed() {
    if (!validAmount.value || processing.value) return;
    processing.value = true;

    try {
        const { data } = await window.axios.post(route('donate.pay'), {
            campaign_id: props.campaign.id,
            amount: finalAmount.value,
            is_anonymous: isAnonymous.value,
        });

        router.visit(route('donate.success', data.donation_id));
    } catch (e) {
        const msg = e?.response?.data?.error || 'Could not process donation.';
        showToast(msg, 'error');
        processing.value = false;
    }
}
</script>

<template>
    <Transition
        enter-active-class="transition duration-250 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
    >
        <div
            v-if="open"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            @click.self="close"
        >
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <div class="flex items-start justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Donate to {{ campaign.title }}
                    </h2>
                    <button type="button" class="text-gray-400 hover:text-gray-700" @click="close">&times;</button>
                </div>
                <p class="mt-1 text-xs text-gray-500">Step {{ step }} of 2</p>

                <div v-if="step === 1" class="mt-5">
                    <p class="text-sm font-medium text-gray-700">Choose amount</p>
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        <button
                            v-for="p in presets"
                            :key="p"
                            type="button"
                            :class="[
                                'rounded-lg border px-3 py-2 text-sm font-medium transition',
                                customAmount === '' && amount === p
                                    ? 'border-maroon-600 bg-maroon-50 text-maroon-700'
                                    : 'border-gray-300 text-gray-700 hover:bg-gray-50',
                            ]"
                            @click="pickPreset(p)"
                        >
                            {{ formatINR(p) }}
                        </button>
                    </div>
                    <div class="mt-3">
                        <label class="text-sm text-gray-600">Custom amount (₹)</label>
                        <input
                            v-model="customAmount"
                            type="number"
                            min="100"
                            max="1000000"
                            placeholder="Enter amount"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-maroon-500 focus:ring-maroon-500"
                        />
                        <p v-if="!validAmount" class="mt-1 text-xs text-red-600">
                            Amount must be between ₹100 and ₹10,00,000.
                        </p>
                    </div>
                    <label class="mt-4 flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" v-model="isAnonymous" class="rounded border-gray-300 text-maroon-600 focus:ring-maroon-500" />
                        Make my donation anonymous
                    </label>
                    <button
                        type="button"
                        :disabled="!validAmount"
                        class="mt-5 w-full rounded-lg bg-maroon-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-maroon-700 disabled:opacity-50"
                        @click="step = 2"
                    >
                        Continue
                    </button>
                </div>

                <div v-else class="mt-5">
                    <p class="text-sm text-gray-700">
                        You're donating
                        <span class="font-semibold">{{ formatINR(finalAmount) }}</span>
                        to <span class="font-semibold">{{ campaign.title }}</span>.
                    </p>
                    <div class="mt-3 rounded-lg bg-gray-50 p-3 text-sm text-gray-600">
                        <p>{{ user.name }}</p>
                        <p>{{ user.email }}</p>
                        <p v-if="isAnonymous" class="mt-1 text-xs text-gray-500">
                            Your name will be hidden from the public donor list.
                        </p>
                    </div>
                    <div class="mt-5 flex gap-2">
                        <button
                            type="button"
                            class="rounded-lg px-4 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-900"
                            @click="step = 1"
                        >
                            Back
                        </button>
                        <button
                            type="button"
                            :disabled="processing"
                            class="flex-1 rounded-lg bg-maroon-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-maroon-700 disabled:opacity-60"
                            @click="proceed"
                        >
                            {{ processing ? 'Processing…' : 'Confirm Donation' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>
