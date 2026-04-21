<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const isMenuOpen = ref(false);
const isDropdownOpen = ref(false);

const navigation = [
    { name: 'Dashboard', href: route('dashboard'), active: route().current('dashboard') },
    { name: 'Hire Service', href: route('hire.apply'), active: route().current('hire.apply') },
];
</script>

<template>
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="shrink-0 flex items-center gap-2">
                        <div
                            class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold shadow-blue-200 shadow-lg">
                            N
                        </div>
                        <span class="text-xl font-bold tracking-tight text-slate-900 uppercase">Notivra</span>
                    </a>

                    <div class="hidden sm:ml-8 sm:flex sm:space-x-4">
                        <Link v-for="item in navigation" :key="item.name" :href="item.href"
                            :class="[item.active ? 'text-blue-600 border-blue-600' : 'text-slate-500 border-transparent hover:text-slate-700 hover:border-slate-300',
                                'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors h-16']">
                            {{ item.name }}
                        </Link>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div v-if="user" class="relative">
                        <div @click="isDropdownOpen = !isDropdownOpen"
                            class="hidden sm:flex items-center gap-3 bg-slate-50 py-1.5 px-3 rounded-full border border-slate-200 cursor-pointer hover:bg-slate-100 transition">
                            <span class="text-sm font-medium text-slate-700">{{ user.name }}</span>
                            <div
                                class="w-7 h-7 bg-slate-300 rounded-full flex items-center justify-center text-[10px] text-white">
                                {{ user.name.charAt(0) }}
                            </div>
                        </div>

                        <div v-if="isDropdownOpen"
                            class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-lg border border-slate-100 py-1 z-50">
                            <Link :href="route('logout')" method="post" as="button"
                                class="block w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                Logout
                            </Link>
                        </div>
                    </div>

                    <button @click="isMenuOpen = !isMenuOpen" class="sm:hidden text-slate-500 p-2">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path v-if="!isMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div v-show="isMenuOpen" class="sm:hidden bg-white border-b border-slate-200">
            <div class="pt-2 pb-3 space-y-1">
                <Link v-for="item in navigation" :key="item.name" :href="item.href"
                    class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition duration-150 ease-in-out"
                    :class="item.active
                        ? 'bg-blue-50 border-blue-500 text-blue-700'
                        : 'border-transparent text-slate-500 hover:bg-slate-50 hover:border-slate-300 hover:text-slate-700'">
                    {{ item.name }}
                </Link>

                <Link :href="route('logout')" method="post" as="button"
                    class="block w-full text-left pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-red-600 hover:bg-red-50 hover:border-red-300 transition duration-150 ease-in-out">
                    Logout
                </Link>
            </div>
        </div>
    </nav>
</template>
