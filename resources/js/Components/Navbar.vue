<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const isMenuOpen = ref(false);
const isDropdownOpen = ref(false);

defineProps({
    user: Object,
    navigation: Array
});

const navigation = [
    { name: 'Dashboard', href: route('dashboard'), active: usePage().url.startsWith('/dashboard') },
    { name: 'Hire Service', href: route('hire.apply'), active: route().current('hire.apply') },
    {
        name: 'Resources',
        children: [
            { name: 'Documentation', href: '/docs' },
            { name: 'API Reference', href: '/api' }
        ]
    }
]

</script>

<template>
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                <div class="flex items-center">
                    <a href="/" class="shrink-0 flex items-center gap-2">
                        <div
                            class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold shadow-blue-200 shadow-lg">
                            N
                        </div>
                        <span class="text-xl font-bold tracking-tight text-slate-900 uppercase">Notivra</span>
                    </a>

                    <div class="hidden md:ml-8 md:flex md:space-x-4 h-full">
                        <template v-for="item in navigation" :key="item.name">
                            <div v-if="item.children" class="relative group flex items-center">
                                <button
                                    class="text-slate-500 hover:text-slate-700 px-1 pt-1 text-sm font-medium inline-flex items-center gap-1 transition-colors h-16 border-b-2 border-transparent">
                                    {{ item.name }}
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div
                                    class="absolute left-0 top-16 w-48 bg-white border border-slate-100 rounded-b-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                    <Link v-for="child in item.children" :key="child.name" :href="child.href"
                                        class="block px-4 py-3 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600">
                                        {{ child.name }}
                                    </Link>
                                </div>
                            </div>

                            <Link v-else :href="item.href"
                                :class="[item.active ? 'text-blue-600 border-blue-600' : 'text-slate-500 border-transparent hover:text-slate-700 hover:border-slate-300',
                                    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors h-20']">
                                {{ item.name }}
                            </Link>
                        </template>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <template v-if="!user">
                        <Link :href="route('login')"
                            class="hidden sm:block text-sm font-medium text-slate-600 hover:text-blue-600">
                            Log in
                        </Link>
                        <Link :href="route('register')"
                            class="hidden bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 shadow-sm transition">
                            Sign Up
                        </Link>
                    </template>

                    <div v-else class="relative">
                        <button @click="isDropdownOpen = !isDropdownOpen"
                            class="flex items-center gap-3 bg-slate-50 py-1.5 px-3 rounded-full border border-slate-200 hover:bg-slate-100 transition">
                            <span class="hidden sm:inline text-sm font-medium text-slate-700">{{ user.name
                                }}</span>
                            <div
                                class="w-7 h-7 bg-blue-100 rounded-full flex items-center justify-center text-[10px] font-bold text-blue-600">
                                {{ user.name.charAt(0).toUpperCase() }}
                            </div>
                        </button>

                        <div v-if="isDropdownOpen" @click.away="isDropdownOpen = false"
                            class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-2xl border border-slate-100 py-2 z-50">
                            <div class="px-4 py-2 border-b border-slate-50 mb-1">
                                <p class="text-xs text-slate-400">Signed in as</p>
                                <p class="text-sm font-semibold truncate text-slate-700">{{ user.email }}</p>
                            </div>
                            <Link :href="route('profile.edit')"
                                class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Profile
                                Settings</Link>
                            <Link :href="route('logout')" method="post" as="button"
                                class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                Logout
                            </Link>
                        </div>
                    </div>

                    <button @click="isMenuOpen = !isMenuOpen"
                        class="md:hidden text-slate-500 p-2 hover:bg-slate-50 rounded-lg">
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

        <transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
            <div v-show="isMenuOpen" class="md:hidden bg-white border-b border-slate-200 overflow-hidden">
                <div class="pt-2 pb-6 space-y-1">
                    <template v-for="item in navigation" :key="item.name">
                        <template v-if="item.children">
                            <div class="px-4 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                {{
                                    item.name }}</div>
                            <Link v-for="child in item.children" :key="child.name" :href="child.href"
                                class="block pl-8 pr-4 py-2 text-base font-medium text-slate-600 hover:bg-slate-50">
                                {{ child.name }}
                            </Link>
                        </template>

                        <Link v-else :href="item.href"
                            class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition"
                            :class="item.active ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-slate-500 hover:bg-slate-50 hover:border-slate-300'">
                            {{ item.name }}
                        </Link>
                    </template>

                    <div v-if="!user" class="pt-4 mt-4 border-t border-slate-100 px-4">
                        <Link :href="route('login')" class="block w-full text-center py-2 text-slate-600 font-medium">
                            Login
                        </Link>
                    </div>
                </div>
            </div>
        </transition>
    </nav>
</template>
