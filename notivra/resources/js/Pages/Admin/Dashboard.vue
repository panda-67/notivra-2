<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';

// Menerima data stat dari DashboardController
defineProps({
    stats: Array
});

</script>

<template>
    <AdminLayout title="Dashboard">

        <div class="space-y-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-950">Selamat Datang Kembali!</h2>
                <p class="text-sm text-gray-500">Berikut adalah ringkasan aktivitas riset dan draf akademik hari ini.
                </p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div v-for="(stat, index) in stats" :key="index"
                    class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
                    <span class="text-sm font-medium text-gray-400 block mb-2">{{ stat.label }}</span>
                    <span class="text-3xl font-bold text-gray-800 tracking-tight">{{ stat.value }}</span>
                </div>
            </div>

            <!-- Placeholder untuk grafik atau log aktivitas -->
            <div
                class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 h-64 flex items-center justify-center text-gray-400">
                Grafik log aktivitas atau antrean draf riset teranyar akan ditempatkan di sini.
            </div>
        </div>
    </AdminLayout>
</template>
