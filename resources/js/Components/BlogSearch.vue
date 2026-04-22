<script setup>
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    initialValue: String
});

const searchQuery = ref(props.initialValue || '');
const isTyping = ref(false);

const performSearch = debounce((query) => {
    isTyping.value = false;

    const baseUrl = window.location.origin + '/';
    const url = new URL(baseUrl);

    if (query) {
        url.searchParams.set('search', query);
        url.searchParams.delete('page');
    } else {
        url.searchParams.delete('search');
    }

    window.location.href = url.toString();
}, 400);

watch(searchQuery, (newVal) => {
    isTyping.value = true;
    performSearch(newVal);
});

</script>

<template>
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <h3 class="font-bold text-slate-800 mb-4">Pencarian</h3>
        <div class="relative">
            <input v-model="searchQuery" type="text" placeholder="Cari artikel..."
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
            <div v-if="isTyping" class="absolute right-3 top-2.5">
                <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </div>
        </div>
    </div>
</template>
