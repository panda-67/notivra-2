<script setup>
import { computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const props = defineProps({
    initialLocale: String
});

const page = usePage();
const currentLang = computed(() => {
    return page?.props?.locale || props.initialLocale || 'en';
});

const switchLocale = (lang) => {
    if (lang === currentLang.value) return;

    // Simpan di cookie (1 tahun) agar dibaca oleh Middleware Laravel
    document.cookie = `locale=${lang}; path=/; max-age=31536000; SameSite=Lax`;

    // Reload halaman untuk mengaplikasikan perubahan bahasa di sisi server
    if (router.page) {
        router.reload({
            preserveScroll: true, // Opsional: menjaga posisi scroll
            onSuccess: () => {
                // Logika tambahan jika diperlukan setelah reload
            }
        });
    } else {
        window.location.reload();
    }
};

const nextLang = computed(() => currentLang.value === 'en' ? 'id' : 'en');
const flagCode = computed(() => currentLang.value === 'en' ? '🇮🇩' : '🇬🇧');
</script>

<template>
    <button @click="switchLocale(nextLang)"
        :aria-label="`Switch language to ${nextLang === 'id' ? 'Bahasa Indonesia' : 'English'}`"
        :title="nextLang === 'id' ? 'Bahasa Indonesia' : 'English'"
        class="flex items-center gap-2 px-2 py-1 cursor-pointer rounded-md border border-slate-300 hover:bg-slate-100 transition group">
        <span class="text-sm font-semibold flex items-center gap-1.5 text-slate-700">
            <span class="text-base leading-none">{{ flagCode }}</span>
            <span class="uppercase">{{ nextLang }}</span>
        </span>
    </button>
</template>

<style scoped>
/* Tambahkan styling tambahan jika diperlukan */
</style>
