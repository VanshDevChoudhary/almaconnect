<script setup>
import { ref, computed, onMounted } from 'vue';
import { gsap } from 'gsap';
import { prefersReducedMotion } from '@/composables/useAuthAnimation';

const props = defineProps({
    raised: { type: Number, default: 0 },
    target: { type: Number, default: 0 },
});

const bar = ref(null);

const pct = computed(() => {
    if (!props.target) return props.raised > 0 ? 100 : 0;
    return Math.min(100, Math.round((props.raised / props.target) * 100));
});

onMounted(() => {
    if (!bar.value) return;
    if (prefersReducedMotion()) {
        bar.value.style.width = pct.value + '%';
        return;
    }
    gsap.fromTo(
        bar.value,
        { width: '0%' },
        { width: pct.value + '%', duration: 1, ease: 'power2.out' },
    );
});
</script>

<template>
    <div>
        <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
            <div ref="bar" class="h-full rounded-full bg-indigo-600" :style="{ width: pct + '%' }"></div>
        </div>
        <p class="mt-1 text-xs text-gray-500">{{ pct }}% funded</p>
    </div>
</template>
