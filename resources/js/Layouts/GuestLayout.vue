<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-white">
        <div class="flex items-center">
            <Link href="/" class="shrink-0 flex items-center gap-2">
                <div
                    class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold shadow-blue-200 shadow-lg">
                    N
                </div>
                <span class="text-xl font-bold tracking-tight text-slate-900 uppercase">Notivra</span>
            </Link>
        </div>

        <div
            class="w-full sm:max-w-7xl mt-6 shadow-xl shadow-slate-200/50 overflow-hidden sm:rounded-3xl border border-slate-100">
            <div class="flex flex-col justify-center items-center bg-slate-100 p-6">
                <slot />
            </div>
        </div>
    </div>
</template>
