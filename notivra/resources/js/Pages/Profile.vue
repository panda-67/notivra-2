<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, Transition } from 'vue';

const user = usePage().props.auth.user;
const recentlySuccessful = ref(false);

const form = useForm({
    name: user.name,
    email: user.email,
});

const updateProfile = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true, // Agar halaman tidak loncat ke atas setelah submit
        onSuccess: () => {
            recentlySuccessful.value = true;
            setTimeout(() => recentlySuccessful.value = false, 2000);
        },
    });
};
</script>

<template>
    <AppLayout title="Profile">
        <template #header>
            <div class="mx-auto max-w-7xl px-4 py-2 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Profile</h1>
                        <p class="text-slate-500 text-sm mt-1">Lengkapi detail profile Anda.</p>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                            <p v-if="recentlySuccessful" class="text-sm mr-2 text-green-600">
                                Berhasil disimpan.
                            </p>
                        </Transition>

                        <button @click="updateProfile" :disabled="form.processing"
                            class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-md hover:bg-indigo-700 transition disabled:opacity-50">
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>Simpan Perubahan</span>
                        </button>

                        <Link :href="route('dashboard')"
                            class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-lg transition-all">
                            Batal
                        </Link>
                    </div>
                </div>
            </div>
        </template>

        <div class="p-8">
            <form @submit.prevent="updateProfile" class="space-y-6">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                    <input v-model="form.name" type="text"
                        :class="{ 'border-red-300 bg-red-50/30': form.errors.name, 'border-slate-200': !form.errors.name }"
                        class="w-full rounded-xl border mt-1 px-2 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 py-3 transition-all">
                    <p v-if="form.errors.name"
                        class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                        {{ form.errors.name }}</p>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input v-model="form.email" type="email"
                        :class="{ 'border-red-300 bg-red-50/30': form.errors.email, 'border-slate-200': !form.errors.email }"
                        class="w-full rounded-xl border mt-1 px-2 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 py-3 transition-all">
                    <p v-if="form.errors.email"
                        class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                        {{ form.errors.email }}</p>

                </div>
            </form>

        </div>
    </AppLayout>
</template>
