import { gsap } from 'gsap';

export function prefersReducedMotion() {
    return (
        typeof window !== 'undefined' &&
        window.matchMedia != null &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches
    );
}

export function safeAnimate(target, opts) {
    if (prefersReducedMotion()) {
        const { duration: _d, ease: _e, stagger: _s, delay: _dl, ...finalState } = opts;
        return gsap.set(target, finalState);
    }
    return gsap.to(target, opts);
}

export function safeFrom(target, opts) {
    if (prefersReducedMotion()) return;
    return gsap.from(target, opts);
}
