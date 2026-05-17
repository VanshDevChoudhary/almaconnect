<script setup>
import { computed } from 'vue';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    size: {
        type: String,
        default: 'md',
        validator: (v) => ['sm', 'md', 'lg', 'xl'].includes(v),
    },
    ring: {
        type: Boolean,
        default: false,
    },
});

const sizeClasses = {
    sm: 'h-8 w-8 text-xs',
    md: 'h-10 w-10 text-sm',
    lg: 'h-16 w-16 text-lg',
    xl: 'h-32 w-32 text-3xl',
};

const palette = [
    '#4f46e5', '#0891b2', '#db2777', '#16a34a', '#ea580c',
    '#7c3aed', '#0d9488', '#dc2626', '#2563eb', '#9333ea',
];

const initials = computed(() => {
    const name = (props.user?.name || '').trim();
    if (!name) return '?';
    const parts = name.split(/\s+/);
    if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
});

const bgColor = computed(() => {
    const id = Number(props.user?.id) || 0;
    return palette[id % palette.length];
});

const avatarUrl = computed(() =>
    props.user?.avatar ? `/storage/${props.user.avatar}` : null,
);
</script>

<template>
    <img
        v-if="avatarUrl"
        :src="avatarUrl"
        :alt="user.name"
        :class="[
            sizeClasses[size],
            'rounded-full object-cover',
            ring ? 'border-4 border-white shadow-xl' : '',
        ]"
    />
    <div
        v-else
        :class="[
            sizeClasses[size],
            'flex items-center justify-center rounded-full font-semibold text-white',
            ring ? 'border-4 border-white shadow-xl' : '',
        ]"
        :style="{ backgroundColor: bgColor }"
        :aria-label="user.name"
    >
        {{ initials }}
    </div>
</template>
