import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useLanguage() {
    const locale = computed(() => usePage().props.locale);
    return { locale };
}
