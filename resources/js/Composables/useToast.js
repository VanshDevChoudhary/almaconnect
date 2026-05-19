import { ref } from 'vue';

const toasts = ref([]);
let seq = 0;

export function useToast() {
    function showToast(message, variant = 'success', duration = 4000) {
        const id = ++seq;
        toasts.value.push({ id, message, variant });
        setTimeout(() => dismiss(id), duration);
        return id;
    }

    function dismiss(id) {
        const i = toasts.value.findIndex((t) => t.id === id);
        if (i !== -1) toasts.value.splice(i, 1);
    }

    return { toasts, showToast, dismiss };
}
