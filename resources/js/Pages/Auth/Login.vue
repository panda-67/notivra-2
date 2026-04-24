<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';

const props = defineProps({
    prefilledEmail: String,
    isUserExist: Boolean,
    username: String,
    isValid: Boolean,
    draft: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
    draft: props.draft,
});

console.log(props.isValid);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
            form.reset('draft');
        }
    });
};
</script>

<template>
    <GuestLayout title="Masuk Ke Notivra">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center w-full max-w-6xl mx-auto p-4 sm:p-8">

            <div class="hidden lg:flex flex-col space-y-6">
                <div
                    class="inline-flex items-center px-4 py-2 rounded-full bg-indigo-50 border border-indigo-100 w-fit">
                    <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest">Academic Support
                        System</span>
                </div>

                <h1 class="text-5xl font-black text-slate-900 leading-tight">
                    Lanjutkan <span class="text-blue-600">Riset</span> <br />
                    Anda Hari Ini.
                </h1>

                <p class="text-lg text-slate-600 leading-relaxed max-w-md">
                    Masuk untuk mengakses draf naskah, data referensi, dan kolaborasi riset terbaru Anda di ekosistem
                    Notivra.
                </p>

                <div class="space-y-3 pt-4">
                    <div class="flex items-center gap-3">
                        <div class="p-1 bg-blue-100 rounded-full text-blue-600">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z">
                                </path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-slate-700">Akses Literatur & Draf Ilmiah</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="p-1 bg-blue-100 rounded-full text-blue-600">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z">
                                </path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-slate-700">Panel Kolaborasi Peneliti</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-center lg:justify-end">
                <div
                    class="w-full sm:max-w-115 bg-white p-8 sm:p-10 shadow-2xl shadow-slate-200/60 rounded-[2.5rem] border border-slate-100">

                    <div class="mb-8 text-center sm:text-left">
                        <div v-if="isValid">
                            <h2 class="text-2xl font-bold text-slate-900 leading-tight">
                                {{ isUserExist ? `Selamat datang kembali, ${username}!` : `Halo ${username}, selangkah
                                lagi!` }}
                            </h2>
                            <p class="text-slate-500 mt-2 text-sm">
                                {{ isUserExist
                                    ? 'Masukkan password akun Anda untuk melanjutkan.'
                                    : 'Tentukan password untuk menyimpan proyek akademik Anda.'
                                }}
                            </p>
                        </div>
                        <div v-else>
                            <h2 class="text-2xl font-bold text-slate-900">Masuk</h2>
                            <p class="text-slate-500 mt-1">Gunakan akun email Anda.</p>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div v-if="!draft">
                            <label class="text-xs font-bold text-slate-500 uppercase ml-1 mb-1 block">Email </label>
                            <input v-model="form.email" type="email" placeholder="nama@universitas.ac.id"
                                class="w-full rounded-2xl border-slate-200 px-4 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 py-2 transition-all placeholder:text-slate-300"
                                :class="{ 'border-red-500': form.errors.email }">
                            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                        </div>

                        <div v-else class="bg-slate-50 p-4 rounded-2xl border border-slate-100 text-center mb-4">
                            <p class="text-xs text-slate-400 uppercase font-bold tracking-widest mb-1">Email Terdeteksi
                            </p>
                            <p class="text-sm font-bold text-slate-700">{{ prefilledEmail }}</p>
                        </div>

                        <div>
                            <div class="flex justify-between items-end mb-1">
                                <label class="text-xs font-bold text-slate-500 uppercase ml-1 block">Password</label>
                                <!-- <Link v-if="!draft" :href="route('password.request')" -->
                                <!--     class="text-[10px] font-bold text-blue-600 hover:underline">Lupa Password?</Link> -->
                            </div>
                            <input v-model="form.password" type="password" placeholder="••••••••"
                                class="w-full rounded-2xl border-slate-200 px-4 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 py-2 transition-all placeholder:text-slate-300"
                                :class="{ 'border-red-500': form.errors.password }">
                            <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}
                            </p>
                        </div>

                        <div class="flex items-center px-1">
                            <input type="checkbox" v-model="form.remember" id="remember"
                                class="rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                            <label for="remember" class="ml-2 text-sm text-slate-600 select-none cursor-pointer">Ingat
                                saya di perangkat ini</label>
                        </div>

                        <button type="submit"
                            class="w-full inline-flex justify-center items-center px-6 py-4 bg-slate-900 border border-transparent rounded-2xl font-bold text-white hover:bg-black active:scale-[0.98] transition-all shadow-xl shadow-slate-200 mt-2"
                            :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                            {{ draft ? (isUserExist ? 'Masuk & Lanjutkan' : 'Buat Akun & Lanjutkan') : 'Login Peneliti'
                            }}
                        </button>
                    </form>

                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <span class="w-full border-t border-slate-100"></span>
                        </div>
                        <div class="relative flex justify-center text-xs uppercase font-mono">
                            <span class="px-3 bg-white text-slate-400 font-medium tracking-tighter">
                                Atau {{ !draft ? 'melalui' : '' }}
                            </span>
                        </div>
                    </div>

                    <a :href="route('auth.google', { d: draft })"
                        class="w-full flex justify-center items-center px-4 py-3.5 border border-slate-200 rounded-2xl shadow-sm text-sm font-bold text-slate-700 bg-white hover:bg-slate-50 transition duration-150 group">
                        <img src="https://www.svgrepo.com/show/475656/google-color.svg"
                            class="w-5 h-5 mr-3 group-hover:grayscale-[0.5] transition" alt="Google">
                        {{ draft ? 'Lanjutkan dengan Google' : 'Akun Google' }}
                    </a>

                    <p class="mt-8 text-center text-sm text-slate-500">
                        Belum memiliki akses?
                        <Link :href="route('register')"
                            class="text-blue-600 font-bold hover:text-blue-700 underline underline-offset-4 ml-1">
                            Daftar sekarang</Link>
                    </p>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
