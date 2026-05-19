import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js', 'resources/js/landing.js'],
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        VitePWA({
            registerType: 'autoUpdate',
            injectRegister: false, // registered manually in app.js
            strategies: 'generateSW',
            manifest: {
                name: 'AlmaConnect',
                short_name: 'AlmaConnect',
                description: 'The alumni network for our institute graduates.',
                start_url: '/',
                display: 'standalone',
                orientation: 'portrait',
                background_color: '#ffffff',
                theme_color: '#4F46E5',
                icons: [
                    { src: '/icons/icon-192.png', sizes: '192x192', type: 'image/png' },
                    { src: '/icons/icon-512.png', sizes: '512x512', type: 'image/png' },
                    { src: '/icons/icon-maskable-512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' },
                ],
                categories: ['education', 'social', 'productivity'],
            },
            workbox: {
                navigateFallback: '/offline',
                navigateFallbackDenylist: [/^\/api\//, /^\/webhooks\//],
                runtimeCaching: [
                    {
                        urlPattern: /^\/storage\/.*\.(png|jpg|jpeg|webp|gif)$/,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'images',
                            expiration: { maxAgeSeconds: 7 * 24 * 60 * 60 },
                        },
                    },
                    {
                        urlPattern: /checkout\.razorpay\.com/,
                        handler: 'NetworkOnly',
                    },
                ],
            },
        }),
    ],
});
