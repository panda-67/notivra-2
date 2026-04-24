<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

</script>

<template>
    <GuestLayout title="Daftar Peneliti - Notivra">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center w-full max-w-6xl mx-auto p-4 sm:p-8">

            <div class="hidden lg:flex flex-col space-y-6">
                <div
                    class="inline-flex items-center px-4 py-2 rounded-full bg-indigo-50 border border-indigo-100 w-fit">
                    <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest">Academic Support
                        System</span>
                </div>

                <h1 class="text-5xl font-black text-slate-900 leading-tight">
                    Akselerasi <span class="text-blue-600">Riset</span> <br />
                    & Publikasi Anda.
                </h1>

                <p class="text-lg text-slate-600 leading-relaxed max-w-md">
                    Notivra hadir untuk mendukung perjalanan akademik Anda. Kelola draf naskah, literatur, dan
                    kolaborasi riset dalam satu ekosistem yang terorganisir.
                </p>

                <div class="space-y-3 pt-4">
                    <div class="flex items-center gap-3">
                        <div class="p-1 bg-green-100 rounded-full">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-slate-700">Manajemen Draf Karya Ilmiah</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="p-1 bg-green-100 rounded-full">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-slate-700">Penyimpanan Data Riset Terpusat</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="p-1 bg-green-100 rounded-full">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-slate-700">Kolaborasi Antar Akademisi</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-center lg:justify-end mt-14 md:mt-0">
                <div
                    class="w-full sm:max-w-115 bg-white p-8 sm:p-10 shadow-2xl shadow-slate-200/60 rounded-[2.5rem] border border-slate-100">

                    <div class="mb-8 text-center sm:text-left">
                        <h2 class="text-2xl font-bold text-slate-900">Gabung sekarang</h2>
                        <p class="text-slate-500 mt-1">Dapatkan dukungan penuh dari kami.</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase ml-1 mb-1 block">Nama </label>
                            <input v-model="form.name" type="text" placeholder="Contoh: Dr. Aris, M.T."
                                class="w-full rounded-2xl border-slate-200 px-4 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 py-2 transition-all placeholder:text-slate-300"
                                :class="{ 'border-red-500': form.errors.name }">
                            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase ml-1 mb-1 block">Email </label>
                            <input v-model="form.email" type="email" placeholder="nama@universitas.ac.id"
                                class="w-full rounded-2xl border-slate-200 px-4 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 py-2 transition-all placeholder:text-slate-300"
                                :class="{ 'border-red-500': form.errors.email }">
                            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase ml-1 mb-1 block">Password</label>
                            <input v-model="form.password" type="password" placeholder="••••••••"
                                class="w-full rounded-2xl border-slate-200 px-4 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 py-2 transition-all placeholder:text-slate-300">
                            <div v-if="form.errors.password" class="text-red-500 text-xs">{{ form.errors.password }}
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase ml-1 mb-1 block">Konfirmasi
                                Password</label>
                            <input v-model="form.password_confirmation" type="password" placeholder="••••••••"
                                class="w-full rounded-2xl border-slate-200 px-4 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 py-2 transition-all placeholder:text-slate-300">
                            <div v-if="form.errors.password_confirmation" class="text-red-500 text-xs">{{
                                form.errors.password_confirmation }}
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full inline-flex justify-center items-center px-6 py-4 bg-slate-900 border border-transparent rounded-2xl font-bold text-white hover:bg-black active:scale-[0.98] transition-all shadow-xl shadow-slate-200 mt-4"
                            :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                            Daftar Sekarang
                        </button>
                    </form>

                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <span class="w-full border-t border-slate-100"></span>
                        </div>
                        <div class="relative flex justify-center text-xs uppercase">
                            <span class="px-3 bg-white text-slate-400 font-medium font-mono">Atau Melalui</span>
                        </div>
                    </div>

                    <a :href="route('auth.google')"
                        class="w-full flex justify-center items-center px-4 py-3.5 border border-slate-200 rounded-2xl shadow-sm text-sm font-bold text-slate-700 bg-white hover:bg-slate-50 transition duration-150">
                        <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5 mr-3"
                            alt="Google">
                        Akun Google
                    </a>

                    <p class="mt-8 text-center text-sm text-slate-500">
                        Sudah memiliki akses?
                        <Link :href="route('login')"
                            class="text-blue-600 font-bold hover:text-blue-700 underline underline-offset-4 ml-1">
                            Masuk sekarang</Link>
                    </p>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
