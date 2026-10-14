<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';

defineProps({
    serviceRequests: Array
});

// Fungsi penanganan klik cepat untuk memperbarui status proyek lewat Inertia.patch
const updateStatus = (id, targetStatus) => {
    if (confirm(`Apakah Anda yakin ingin mengubah status proyek ${id} menjadi ${targetStatus}?`)) {
        router.patch(route('admin.projects.updateStatus', { id: id }), {
            status: targetStatus
        }, {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <AdminLayout title="Project Lists">
        <div class="space-y-6">
            <!-- Header Halaman -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-950">Manajemen Layanan & Proyek</h2>
                    <p class="text-sm text-gray-500">Pantau dan kelola permintaan bantuan draf akademik atau submit
                        jurnal klien.</p>
                </div>
                <button
                    class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition shadow-sm">
                    + Tambah Proyek Manual
                </button>
            </div>

            <!-- Filter & Pencarian Ringkas -->
            <div
                class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
                <div class="w-full md:w-72">
                    <input type="text" placeholder="Cari nama proyek atau klien..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                </div>
                <div class="flex space-x-2 w-full md:w-auto overflow-x-auto">
                    <button
                        class="bg-slate-100 px-3 py-1.5 text-xs font-medium rounded-md text-slate-700 hover:bg-slate-200">Semua</button>
                    <button
                        class="bg-amber-50 px-3 py-1.5 text-xs font-medium rounded-md text-amber-700">Pending</button>
                    <button
                        class="bg-blue-50 px-3 py-1.5 text-xs font-medium rounded-md text-blue-700">Processing</button>
                    <button
                        class="bg-emerald-50 px-3 py-1.5 text-xs font-medium rounded-md text-emerald-700">Completed</button>
                </div>
            </div>

            <!-- Data Table -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-slate-50 border-b border-gray-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="px-6 py-4">ID / Tanggal</th>
                                <th class="px-6 py-4">Detail Proyek</th>
                                <th class="px-6 py-4">Klien</th>
                                <th class="px-6 py-4">Jenis Layanan</th>
                                <th class="px-6 py-4">Biaya / Estimasi</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                            <tr v-for="item in serviceRequests" :key="item.id" class="hover:bg-slate-50/70 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono text-xs font-semibold text-slate-600 block">{{ item.id
                                    }}</span>
                                    <span class="text-xs text-gray-400 block mt-0.5">{{ item.created_at }}</span>
                                </td>
                                <td class="px-6 py-4 max-w-xs md:max-w-sm truncate">
                                    <span class="font-medium text-gray-900 block truncate" :title="item.project_name">
                                        {{ item.project_name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-800">
                                    {{ item.client }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                    {{ item.service_type }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-gray-900">
                                    {{ item.cost }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full inline-block" :class="{
                                        'bg-amber-100 text-amber-800': item.status === 'Pending',
                                        'bg-blue-100 text-blue-800': item.status === 'Processing',
                                        'bg-emerald-100 text-emerald-800': item.status === 'Completed'
                                    }">
                                        {{ item.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button @click="updateStatus(item.id, 'Processing')"
                                            v-if="item.status === 'Pending'"
                                            class="text-blue-600 hover:underline font-medium">Proses</button>
                                        <button @click="updateStatus(item.id, 'Completed')"
                                            v-if="item.status === 'Processing'"
                                            class="text-emerald-600 hover:underline font-medium">Selesai</button>
                                        <span class="text-gray-300">|</span>
                                        <button class="text-slate-500 hover:text-slate-800 font-medium">Detail</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
