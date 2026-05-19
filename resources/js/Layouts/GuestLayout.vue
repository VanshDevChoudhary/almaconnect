<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const isLogin = computed(() => page.component === 'Auth/Login');
</script>

<template>
    <div class="flex min-h-screen">

        <!-- ── Left: form panel ───────────────────────────────────── -->
        <div class="flex w-full flex-col justify-center overflow-y-auto bg-white px-8 py-12 md:w-1/2 xl:px-16">
            <div class="mx-auto w-full max-w-sm">

                <!-- Logo -->
                <Link href="/" class="mb-10 block text-2xl font-bold tracking-tight text-gray-900">
                    Alma<span class="text-maroon-600">Connect</span>
                </Link>

                <!-- Sliding form -->
                <Transition name="auth-slide" mode="out-in">
                    <div :key="page.component">
                        <slot />
                    </div>
                </Transition>

            </div>
        </div>

        <!-- ── Right: decorative panel ───────────────────────────── -->
        <div class="relative hidden overflow-hidden md:flex md:w-1/2 md:flex-col md:items-center md:justify-center"
             style="background: linear-gradient(145deg, #8b1627 0%, #6b0d1c 50%, #3d0813 100%)">

            <!-- Decorative circles -->
            <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-white/5"></div>
            <div class="absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-white/5"></div>
            <div class="absolute right-12 top-1/3 h-40 w-40 rounded-full bg-white/5"></div>
            <div class="absolute bottom-1/3 left-16 h-24 w-24 rounded-full bg-maroon-600/30"></div>

            <!-- Illustration -->
            <div class="relative z-10 flex flex-col items-center px-12 text-center text-white">

                <!-- SVG illustration -->
                <div class="mb-8">
                    <svg width="220" height="220" viewBox="0 0 220 220" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Outer ring -->
                        <circle cx="110" cy="110" r="100" stroke="white" stroke-opacity="0.1" stroke-width="1.5" stroke-dasharray="6 4"/>
                        <!-- Inner ring -->
                        <circle cx="110" cy="110" r="72" stroke="white" stroke-opacity="0.15" stroke-width="1.5"/>
                        <!-- Center cap base -->
                        <ellipse cx="110" cy="118" rx="42" ry="10" fill="white" fill-opacity="0.15"/>
                        <!-- Mortarboard top -->
                        <polygon points="110,70 155,95 110,108 65,95" fill="white" fill-opacity="0.9"/>
                        <!-- Cap top square shine -->
                        <polygon points="110,70 155,95 110,108 65,95" fill="white" fill-opacity="0.15" transform="translate(2,-2)"/>
                        <!-- Tassel string -->
                        <line x1="155" y1="95" x2="155" y2="122" stroke="white" stroke-opacity="0.7" stroke-width="2"/>
                        <circle cx="155" cy="126" r="4" fill="white" fill-opacity="0.8"/>
                        <!-- Network nodes -->
                        <circle cx="52" cy="62" r="8" fill="white" fill-opacity="0.3"/>
                        <circle cx="168" cy="62" r="6" fill="white" fill-opacity="0.25"/>
                        <circle cx="40" cy="148" r="6" fill="white" fill-opacity="0.25"/>
                        <circle cx="178" cy="155" r="8" fill="white" fill-opacity="0.3"/>
                        <circle cx="110" cy="178" r="6" fill="white" fill-opacity="0.25"/>
                        <!-- Connecting lines -->
                        <line x1="52" y1="62" x2="75" y2="90" stroke="white" stroke-opacity="0.2" stroke-width="1.5"/>
                        <line x1="168" y1="62" x2="145" y2="90" stroke="white" stroke-opacity="0.2" stroke-width="1.5"/>
                        <line x1="40" y1="148" x2="68" y2="130" stroke="white" stroke-opacity="0.2" stroke-width="1.5"/>
                        <line x1="178" y1="155" x2="152" y2="130" stroke="white" stroke-opacity="0.2" stroke-width="1.5"/>
                        <line x1="110" y1="178" x2="110" y2="155" stroke="white" stroke-opacity="0.2" stroke-width="1.5"/>
                    </svg>
                </div>

                <h2 class="text-3xl font-bold leading-tight">
                    {{ isLogin ? 'New to AlmaConnect?' : 'Welcome back!' }}
                </h2>
                <p class="mt-3 max-w-xs text-base text-white/75">
                    {{ isLogin
                        ? 'Join thousands of graduates. Create your account in minutes.'
                        : 'Sign in to reconnect with your alumni network.' }}
                </p>

                <!-- Switch CTA -->
                <Transition name="panel-fade" mode="out-in">
                    <div :key="isLogin" class="mt-10">
                        <Link
                            v-if="isLogin"
                            :href="route('register')"
                            class="inline-block rounded-xl border-2 border-white/50 px-8 py-3 text-sm font-semibold text-white transition hover:bg-white/10 hover:border-white focus:outline-none focus:ring-2 focus:ring-white/50"
                        >
                            Create account →
                        </Link>
                        <Link
                            v-else
                            :href="route('login')"
                            class="inline-block rounded-xl border-2 border-white/50 px-8 py-3 text-sm font-semibold text-white transition hover:bg-white/10 hover:border-white focus:outline-none focus:ring-2 focus:ring-white/50"
                        >
                            Sign in →
                        </Link>
                    </div>
                </Transition>

                <!-- Stats row -->
                <div class="mt-12 flex items-center gap-8 text-center">
                    <div>
                        <p class="text-2xl font-bold">1,200+</p>
                        <p class="text-xs text-white/60 mt-0.5">Alumni</p>
                    </div>
                    <div class="h-8 w-px bg-white/20"></div>
                    <div>
                        <p class="text-2xl font-bold">24+</p>
                        <p class="text-xs text-white/60 mt-0.5">Events</p>
                    </div>
                    <div class="h-8 w-px bg-white/20"></div>
                    <div>
                        <p class="text-2xl font-bold">150+</p>
                        <p class="text-xs text-white/60 mt-0.5">Mentors</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>
/* Form slides right-in on login, left-in on register */
.auth-slide-enter-active { transition: all 0.32s cubic-bezier(0.4, 0, 0.2, 1); }
.auth-slide-leave-active { transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1); }
.auth-slide-enter-from   { opacity: 0; transform: translateX(32px); }
.auth-slide-leave-to     { opacity: 0; transform: translateX(-32px); }

.panel-fade-enter-active,
.panel-fade-leave-active { transition: all 0.3s ease; }
.panel-fade-enter-from,
.panel-fade-leave-to     { opacity: 0; transform: translateY(8px); }
</style>
