<template>
    <footer class="mt-16 pt-8 border-t border-slate-100">
        <div class="bg-slate-50 p-8 rounded-2xl text-center">
            <h3 class="font-bold text-lg text-slate-800">Suka dengan tulisan ini?</h3>
            <p class="text-slate-600 mb-6">Bagikan ke teman sejawat artikel ini.</p>

            <div class="flex justify-center gap-4">
                <button @click="share"
                    class="bg-blue-600 text-white px-6 py-2 rounded-xl font-semibold shadow-md shadow-blue-200 hover:bg-blue-700 transition flex items-center justify-center gap-2">
                    <span>Bagikan Artikel</span>
                </button>
            </div>

            <transition name="fade">
                <p v-if="copied" class="text-blue-600 text-sm mt-4 font-medium">
                    Link berhasil disalin!
                </p>
            </transition>
        </div>
    </footer>
</template>

<script setup>
import { ref } from 'vue';

const copied = ref(false);

const share = async () => {
    const data = {
        title: document.title,
        url: window.location.href
    };

    if (navigator.share) {
        try {
            await navigator.share(data);
        } catch (err) {
            console.log('Batal berbagi');
        }
    } else {
        await navigator.clipboard.writeText(data.url);
        copied.value = true;
        setTimeout(() => copied.value = false, 3000);
    }
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
