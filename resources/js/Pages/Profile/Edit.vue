<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import UserAvatar from '@/Components/UserAvatar.vue';
import SkillsTagInput from '@/Components/SkillsTagInput.vue';
import Toast from '@/Components/Toast.vue';
import { useToast } from '@/Composables/useToast';
import { prefersReducedMotion } from '@/composables/useAuthAnimation';

const props = defineProps({
    profileUser: { type: Object, required: true },
    profile: { type: Object, required: true },
});

const page = usePage();
const { showToast } = useToast();

const currentYear = new Date().getFullYear();
const years = (() => {
    const list = [];
    for (let y = currentYear + 5; y >= 1980; y--) list.push(y);
    return list;
})();
const branches = ['CSE', 'ECE', 'ME', 'CE', 'EE', 'IT', 'Chemical', 'Civil', 'Other'];
const industries = [
    'Software', 'Finance', 'Consulting', 'Manufacturing', 'Healthcare',
    'Education', 'Government', 'Entrepreneurship', 'Other',
];
const cities = [
    'Bangalore', 'Delhi', 'Mumbai', 'Hyderabad', 'Chennai', 'Pune',
    'Kolkata', 'Ahmedabad', 'Jaipur', 'Gurgaon', 'Noida',
];

const form = useForm({
    name: props.profileUser.name ?? '',
    batch: props.profile.batch ?? '',
    branch: props.profile.branch ?? '',
    roll_no: props.profile.roll_no ?? '',
    current_company: props.profile.current_company ?? '',
    current_role: props.profile.current_role ?? '',
    industry: props.profile.industry ?? '',
    city: props.profile.city ?? '',
    country: props.profile.country ?? 'India',
    bio: props.profile.bio ?? '',
    skills: [...(props.profile.skills ?? [])],
    linkedin_url: props.profile.linkedin_url ?? '',
    website_url: props.profile.website_url ?? '',
});

const bioCount = computed(() => form.bio.length);

const localAvatar = ref(props.profileUser.avatar);
const avatarUser = computed(() => ({
    id: props.profileUser.id,
    name: form.name || props.profileUser.name,
    avatar: localAvatar.value,
}));

const avatarInput = ref(null);
const avatarPreview = ref(null);
const uploading = ref(false);

function pickAvatar() {
    avatarInput.value?.click();
}

async function onAvatarChange(e) {
    const file = e.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (ev) => (avatarPreview.value = ev.target.result);
    reader.readAsDataURL(file);

    uploading.value = true;
    const data = new FormData();
    data.append('avatar', file);

    try {
        const res = await window.axios.post(route('profile.avatar'), data, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        localAvatar.value = res.data.avatar;
        avatarPreview.value = null;
        showToast('Profile photo updated.');
    } catch (err) {
        const msg =
            err?.response?.data?.errors?.avatar?.[0] ||
            'Could not upload that image.';
        showToast(msg, 'error');
        avatarPreview.value = null;
    } finally {
        uploading.value = false;
        if (avatarInput.value) avatarInput.value.value = '';
    }
}

const cancelHref = computed(() =>
    props.profile.slug
        ? route('profile.show', props.profile.slug)
        : route('dashboard'),
);

function submit() {
    form.patch(route('profile.update'), { preserveScroll: true });
}

const sections = ref(null);

onMounted(() => {
    if (page.props.flash?.success) {
        showToast(page.props.flash.success);
    }
    if (!sections.value || prefersReducedMotion()) return;
    const cards = sections.value.querySelectorAll('[data-section]');
    gsap.from(cards, {
        opacity: 0,
        y: 14,
        duration: 0.3,
        stagger: 0.08,
        ease: 'power2.out',
    });
});

watch(
    () => page.props.flash?.success,
    (val) => {
        if (val) showToast(val);
    },
);
</script>

<template>
    <Head title="Edit profile" />
    <Toast />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Edit your profile
            </h2>
        </template>

        <div class="py-10">
            <div ref="sections" class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Basic info -->
                <section data-section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                        Basic info
                    </h3>

                    <div class="mt-4 flex items-center gap-4">
                        <img
                            v-if="avatarPreview"
                            :src="avatarPreview"
                            class="h-16 w-16 rounded-full object-cover"
                            alt="Preview"
                        />
                        <UserAvatar v-else :user="avatarUser" size="lg" />
                        <div>
                            <input
                                ref="avatarInput"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                class="hidden"
                                @change="onAvatarChange"
                            />
                            <button
                                type="button"
                                :disabled="uploading"
                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:opacity-60"
                                @click="pickAvatar"
                            >
                                {{ uploading ? 'Uploading…' : 'Upload photo' }}
                            </button>
                            <p class="mt-1 text-xs text-gray-500">
                                JPG, PNG or WebP, up to 5&nbsp;MB.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5">
                        <InputLabel for="name" value="Full name" />
                        <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="mt-5">
                        <InputLabel value="Email (can't be changed)" />
                        <p class="mt-1 text-sm text-gray-500">{{ profileUser.email }}</p>
                    </div>
                </section>

                <!-- Academic -->
                <section data-section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                        Academic
                    </h3>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <InputLabel for="batch" value="Graduation year" />
                            <select id="batch" v-model="form.batch" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-maroon-500 focus:ring-maroon-500">
                                <option value="">—</option>
                                <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.batch" />
                        </div>
                        <div>
                            <InputLabel for="branch" value="Branch" />
                            <select id="branch" v-model="form.branch" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-maroon-500 focus:ring-maroon-500">
                                <option value="">—</option>
                                <option v-for="b in branches" :key="b" :value="b">{{ b }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.branch" />
                        </div>
                        <div>
                            <InputLabel for="roll_no" value="Roll number" />
                            <TextInput id="roll_no" v-model="form.roll_no" type="text" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.roll_no" />
                        </div>
                    </div>
                </section>

                <!-- Professional -->
                <section data-section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                        Professional
                    </h3>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="current_company" value="Current company" />
                            <TextInput id="current_company" v-model="form.current_company" type="text" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.current_company" />
                        </div>
                        <div>
                            <InputLabel for="current_role" value="Current role" />
                            <TextInput id="current_role" v-model="form.current_role" type="text" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.current_role" />
                        </div>
                        <div>
                            <InputLabel for="industry" value="Industry" />
                            <select id="industry" v-model="form.industry" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-maroon-500 focus:ring-maroon-500">
                                <option value="">—</option>
                                <option v-for="i in industries" :key="i" :value="i">{{ i }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.industry" />
                        </div>
                    </div>
                </section>

                <!-- Location -->
                <section data-section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                        Location
                    </h3>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="city" value="City" />
                            <input
                                id="city"
                                v-model="form.city"
                                list="city-options"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-maroon-500 focus:ring-maroon-500"
                            />
                            <datalist id="city-options">
                                <option v-for="c in cities" :key="c" :value="c" />
                            </datalist>
                            <InputError class="mt-2" :message="form.errors.city" />
                        </div>
                        <div>
                            <InputLabel for="country" value="Country" />
                            <TextInput id="country" v-model="form.country" type="text" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.country" />
                        </div>
                    </div>
                </section>

                <!-- About -->
                <section data-section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                        About
                    </h3>
                    <div class="mt-4">
                        <InputLabel for="bio" value="Bio" />
                        <textarea
                            id="bio"
                            v-model="form.bio"
                            rows="5"
                            maxlength="2000"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-maroon-500 focus:ring-maroon-500"
                        ></textarea>
                        <div class="mt-1 flex justify-between text-xs text-gray-500">
                            <InputError :message="form.errors.bio" />
                            <span>{{ bioCount }} / 2000</span>
                        </div>
                    </div>
                    <div class="mt-5">
                        <InputLabel value="Skills" />
                        <SkillsTagInput v-model="form.skills" :max="20" class="mt-1" />
                        <InputError class="mt-2" :message="form.errors.skills" />
                    </div>
                </section>

                <!-- Links -->
                <section data-section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                        Links
                    </h3>
                    <div class="mt-4 space-y-4">
                        <div>
                            <InputLabel for="linkedin_url" value="LinkedIn URL" />
                            <TextInput id="linkedin_url" v-model="form.linkedin_url" type="url" placeholder="https://linkedin.com/in/…" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.linkedin_url" />
                        </div>
                        <div>
                            <InputLabel for="website_url" value="Personal website" />
                            <TextInput id="website_url" v-model="form.website_url" type="url" placeholder="https://…" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.website_url" />
                        </div>
                    </div>
                </section>

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        :disabled="form.processing"
                        class="rounded-lg bg-maroon-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition active:scale-[0.98] hover:bg-maroon-700 disabled:opacity-60"
                        @click="submit"
                    >
                        {{ form.processing ? 'Saving…' : 'Save changes' }}
                    </button>
                    <Link
                        :href="cancelHref"
                        class="rounded-lg px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:text-gray-900"
                    >
                        Cancel
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
