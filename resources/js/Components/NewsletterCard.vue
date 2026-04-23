<template>
    <div class="bg-blue-600 p-6 rounded-2xl shadow-lg shadow-blue-200 text-white">
        <h3 class="font-bold mb-2">Langganan Insight</h3>
        <p class="text-xs text-blue-100 mb-4">Dapatkan update akademik terbaru langsung di email Anda.</p>

        <form @submit.prevent="submit" v-if="!form.wasSuccessful">
            <div class="space-y-2">
                <input type="email" v-model="form.email" placeholder="Email Anda" required
                    class="w-full px-4 py-2 rounded-xl text-sm text-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    :class="{ 'border-2 border-red-400': form.errors.email }">

                <p v-if="form.errors.email" class="text-[10px] text-red-200">
                    {{ form.errors.email[0] }}
                </p>

                <button type="submit" :disabled="form.processing"
                    class="w-full bg-white text-blue-600 font-semibold py-2 rounded-xl text-sm hover:bg-blue-50 transition disabled:opacity-50">
                    {{ form.processing ? 'Mengirim...' : 'Gabung Sekarang' }}
                </button>
            </div>
        </form>

        <div v-else class="text-center py-2 animate-bounce">
            <p class="text-sm font-bold">✨ Terima kasih telah berlangganan!</p>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue';

const form = reactive({
    email: '',
    processing: false,
    errors: {},
    wasSuccessful: false
});

const submit = async () => {
    form.processing = true;
    form.errors = {};

    try {
        const response = await fetch('/newsletter/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ email: form.email })
        });

        const result = await response.json();

        if (!response.ok) {
            if (response.status === 422) {
                form.errors = result.errors;
            } else {
                throw new Error('Terjadi kesalahan sistem.');
            }
        } else {
            form.wasSuccessful = true;
            form.email = '';
        }
    } catch (error) {
        console.error("Fetch error:", error);
        alert("Gagal mengirim data. Silakan coba lagi.");
    } finally {
        form.processing = false;
    }
};
</script>
