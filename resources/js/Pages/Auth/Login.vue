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
    <div class="w-full max-w-md mx-auto mt-10 p-8 bg-white rounded-3xl shadow-xl border border-slate-100">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-50 text-blue-600 rounded-full mb-4">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <div v-if="isValid">
                <h1 class="text-xl font-bold">
                    {{ isUserExist ? `Selamat datang kembali, ${username}!` : `Halo ${username}, selangkah lagi!` }}
                </h1>
                <p class="text-sm text-slate-500">
                    {{ isUserExist
                        ? 'Masukkan password akun Anda untuk melanjutkan.'
                        : 'Tentukan password untuk membuat akun dan menyimpan proyek Anda.'
                    }}
                </p>
            </div>
        </div>

        <a :href="route('auth.google', { d: draft })"
            class="w-full flex items-center justify-center gap-3 bg-white border border-slate-200 py-3 rounded-xl hover:bg-slate-50 transition-all font-medium text-slate-700">
            <img src="https://cdn.iconscout.com/icon/free/png-512/free-google-icon-svg-download-png-189824.png?f=webp&w=256"
                class="w-5 h-5" alt="Google" />
            Lanjutkan dengan Google
        </a>

        <div class="relative my-8">
            <div class="absolute inset-0 flex items-center">
                <span class="w-full border-t border-slate-300/80"></span>
            </div>
            <div class="relative flex justify-center text-xs uppercase">
                <span class="bg-white px-4 text-slate-400">Atau masukkan password</span>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div v-if="!draft" class="mb-4">
                <input v-model="form.email" type="email" placeholder="Masukkan email ..."
                    class="w-full rounded-xl border px-2 focus:ring-blue-500 focus:border-blue-500 py-3"
                    :class="{ 'border-red-500': form.errors.email, 'border-slate-200': !form.errors.email }">
                <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
            </div>

            <div v-else class="mb-4 text-center">
                <p class="text-sm font-bold">{{ prefilledEmail }}</p>
            </div>

            <div>
                <input v-model="form.password" type="password" placeholder="Masukkan password ..."
                    class="w-full rounded-xl border px-2 focus:ring-blue-500 focus:border-blue-500 py-3"
                    :class="{ 'border-red-500': form.errors.password, 'border-slate-200': !form.errors.password }">
                <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
            </div>

            <div class="flex justify-end items-center mb-2">
                <input type="checkbox" v-model="form.remember" class="rounded text-blue-600">
                <span class="ml-2 text-sm text-slate-600">Ingat saya</span>
            </div>

            <button :disabled="form.processing"
                class="w-full bg-slate-900 text-white py-3 rounded-xl font-bold hover:bg-black transition-all">
                {{ isUserExist ? 'Masuk & Lanjutkan' : 'Buat Akun & Lanjutkan' }}
            </button>
        </form>

        <div class="mt-4 text-center">
            <p class="text-xs text-slate-500">
                Belum punya akun?
                <Link :href="route('register')" class="text-blue-600 font-bold">Daftar sekarang</Link>
            </p>
        </div>
    </div>
</template>

<script>
export default {
    layout: GuestLayout
}
</script>
