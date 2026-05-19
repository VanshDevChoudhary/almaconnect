import { onMounted } from 'vue';
import { gsap } from 'gsap';

export function prefersReducedMotion() {
    return (
        typeof window !== 'undefined' &&
        window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches
    );
}

/**
 * Entrance animation for auth pages:
 *  - the card fades + slides up (350ms ease-out)
 *  - elements marked [data-auth-field] fade in, staggered 50ms / 250ms each
 *
 * Honors prefers-reduced-motion by skipping straight to the final state.
 */
export function useAuthAnimation(cardRef) {
    onMounted(() => {
        const card = cardRef?.value;
        if (!card) return;

        const fields = card.querySelectorAll('[data-auth-field]');

        if (prefersReducedMotion()) {
            gsap.set(card, { opacity: 1, y: 0, clearProps: 'all' });
            gsap.set(fields, { opacity: 1, y: 0, clearProps: 'all' });
            return;
        }

        gsap.from(card, {
            opacity: 0,
            y: 12,
            duration: 0.35,
            ease: 'power2.out',
        });

        if (fields.length) {
            gsap.from(fields, {
                opacity: 0,
                y: 8,
                duration: 0.25,
                ease: 'power2.out',
                stagger: 0.05,
                delay: 0.1,
            });
        }
    });
}
