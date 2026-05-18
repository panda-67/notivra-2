<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    title: String,
});

</script>

<template>
    <div class="flex h-screen bg-gray-100 text-gray-800 font-sans">

        <Head :title="title ? title : ($page.props.title || 'Welcome')" />

        <!-- Sidebar -->
        <aside class="w-64 bg-slate-900 text-white flex-col justify-between hidden md:flex">
            <div>
                <div class="p-5 text-xl font-bold tracking-wider border-b border-slate-800 text-emerald-400">
                    NOTIVRA <span class="text-xs text-white block">Admin Panel</span>
                </div>
                <nav class="p-4 space-y-2">
                    <Link :href="route('admin.dashboard')"
                        class="flex items-center space-x-3 px-4 py-2.5 rounded transition"
                        :class="route().current('admin.dashboard') ? 'bg-emerald-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                        <span>📊 &nbsp; Dashboard</span>
                    </Link>
                    <!-- Menu lain nanti ditambah di sini -->
                    <Link :href="route('admin.projects.index')"
                        class="flex items-center space-x-3 px-4 py-2.5 rounded transition"
                        :class="route().current('admin.projects.*') ? 'bg-emerald-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                        <span>💼 &nbsp; Layanan & Proyek</span>
                    </Link>
                </nav>
            </div>
            <div class="p-4 border-t border-slate-800 text-xs text-slate-500">
                v1.0.0 — © 2026
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Topbar -->
            <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 border-b border-gray-200">
                <h1 class="text-lg font-semibold text-gray-700">Pusat Kendali</h1>
                <div class="flex items-center space-x-4">
                    <span class="text-sm bg-slate-100 px-3 py-1 rounded-full text-slate-600 font-medium">Admin
                        Staff</span>
                </div>
            </header>

            <!-- Content Body -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-6">
                <!-- Slot untuk me-render halaman anak (Pages) -->
                <slot />
            </main>
        </div>
    </div>
</template>
