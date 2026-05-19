import { ref } from 'vue';

const state = ref(null);

export function useConfirm() {
    function confirm({
        title,
        body,
        confirmLabel = 'Confirm',
        cancelLabel = 'Cancel',
        variant = 'danger',
    }) {
        return new Promise((resolve) => {
            state.value = { title, body, confirmLabel, cancelLabel, variant, resolve };
        });
    }

    function handleConfirm() {
        state.value?.resolve(true);
        state.value = null;
    }

    function handleCancel() {
        state.value?.resolve(false);
        state.value = null;
    }

    return { state, confirm, handleConfirm, handleCancel };
}
