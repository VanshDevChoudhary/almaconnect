import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* ── Hero entrance ────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    if (reduced) {
        document.querySelectorAll('[data-hero]').forEach((el) => {
            el.style.opacity = '1';
            el.style.transform = 'none';
        });
        return;
    }

    const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

    const words = document.querySelectorAll('[data-hero-word]');
    const sub = document.querySelector('[data-hero-sub]');
    const ctas = document.querySelectorAll('[data-hero-cta]');
    const stats = document.querySelector('[data-hero-stats]');

    if (words.length) {
        tl.from(words, { opacity: 0, y: 24, stagger: 0.06, duration: 0.7 });
    }
    if (sub) tl.from(sub, { opacity: 0, y: 20, duration: 0.6 }, '-=0.2');
    if (ctas.length) tl.from(ctas, { opacity: 0, y: 18, scale: 0.95, stagger: 0.1, duration: 0.5 }, '-=0.1');
    if (stats) tl.from(stats, { opacity: 0, duration: 0.4 }, '-=0.1');

    /* ── Stat counters ──────────────────────────────────────────────── */
    document.querySelectorAll('[data-counter]').forEach((el) => {
        const end = parseFloat(el.dataset.counter);
        ScrollTrigger.create({
            trigger: el,
            start: 'top 85%',
            once: true,
            onEnter: () => {
                gsap.to({ val: 0 }, {
                    val: end,
                    duration: 1.5,
                    ease: 'power2.out',
                    onUpdate() { el.textContent = formatCounter(this.targets()[0].val, el.dataset.format); },
                    onComplete() { el.textContent = el.dataset.final; },
                });
            },
        });
    });

    /* ── Feature cards stagger ──────────────────────────────────────── */
    const featureCards = document.querySelectorAll('[data-feature-card]');
    if (featureCards.length) {
        gsap.from(featureCards, {
            opacity: 0, y: 32, stagger: 0.08, duration: 0.5, ease: 'power3.out',
            scrollTrigger: { trigger: featureCards[0], start: 'top 80%', once: true },
        });
    }

    /* ── Generic scroll reveals ─────────────────────────────────────── */
    document.querySelectorAll('[data-reveal]').forEach((el) => {
        const dir = el.dataset.reveal;
        const from = dir === 'left' ? { x: -60 } : dir === 'right' ? { x: 60 } : { y: 40 };
        gsap.from(el, {
            ...from, opacity: 0, duration: 0.7, ease: 'power3.out',
            scrollTrigger: { trigger: el, start: 'top 82%', once: true },
        });
    });
});

function formatCounter(val, fmt) {
    if (fmt === 'inr') {
        const n = Math.floor(val);
        if (n >= 10000000) return '₹' + (n / 10000000).toFixed(1) + 'Cr';
        if (n >= 100000) return '₹' + (n / 100000).toFixed(0) + 'L';
        return '₹' + n.toLocaleString('en-IN');
    }
    return Math.floor(val).toLocaleString('en-IN');
}
