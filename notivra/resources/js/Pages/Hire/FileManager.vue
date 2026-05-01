<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({ data: Object });

const uploadFile = (event) => {
    const formData = new FormData();
    formData.append('file', event.target.files[0]);
    router.post(route('file-manager.store', props.data.id), formData);
};

const deleteFile = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus file ini?')) {
        router.delete(route('file-manager.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                // Optional: show a success toast
            }
        });
    }
}
</script>

<template>
    <AppLayout title="File Manager">
        <template #header>
            <div class="mx-auto max-w-7xl px-4 py-2 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Project Files</h1>
                            <span :class="[
                                'px-2.5 py-0.5 mb-3 rounded-full text-xs font-semibold uppercase',
                                data.status === 'final' ? 'bg-green-100 text-green-700' :
                                    data.status === 'processing' ? 'bg-amber-100 text-amber-700' :
                                        'bg-slate-100 text-slate-600'
                            ]">
                                {{ data.status }}
                            </span>
                        </div>
                        <p class="text-slate-500 text-sm mt-1 truncate">
                            Memperbarui detail project: <span class="font-bold italic ml-0.5 text-slate-700">
                                {{ data.topic }} </span>
                        </p>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <Link :href="route('dashboard')"
                            class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-lg transition-all">
                            Batal
                        </Link>
                        <input type="file" @change="uploadFile" class="hidden" id="fileUpload" />
                        <label for="fileUpload"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-lg cursor-pointer hover:bg-indigo-700 transition">
                            Unggah File Baru
                        </label>
                    </div>
                </div>
            </div>
        </template>

        <div class="p-6 bg-gray-50 min-h-screen">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <!-- Added group class for hover effects -->
                <div v-for="file in data.files" :key="file.id"
                    class="group relative bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition cursor-pointer">

                    <!-- Delete Button (Visible on group hover) -->
                    <button @click.stop="deleteFile(file.id)"
                        class="absolute top-2 right-2 p-1.5 bg-red-50 text-red-500 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-500 hover:text-white"
                        title="Hapus File">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>

                    <div class="flex justify-center mb-3">
                        <span v-if="file.mime_type.includes('pdf')" class="text-red-500 text-4xl">📄</span>
                        <span v-else-if="file.mime_type.includes('image')" class="text-green-500 text-4xl">🖼️</span>
                        <span v-else class="text-blue-500 text-4xl">📁</span>
                    </div>

                    <p class="text-sm font-medium text-gray-700 truncate text-center">{{ file.name }}</p>
                    <p class="text-xs text-gray-400 text-center">{{ (file.size / 1024).toFixed(2) }} KB</p>
                </div>
            </div>
        </div>
        <!-- <div class="p-6 bg-gray-50 min-h-screen"> -->
        <!--     <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4"> -->
        <!--         <div v-for="file in data.files" :key="file.id" -->
        <!--             class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition cursor-pointer"> -->
        <!--             <div class="flex justify-center mb-3"> -->
        <!--                 <span v-if="file.mime_type.includes('pdf')" class="text-red-500 text-4xl">📄</span> -->
        <!--                 <span v-else class="text-blue-500 text-4xl">📁</span> -->
        <!--             </div> -->
        <!--             <p class="text-sm font-medium text-gray-700 truncate text-center">{{ file.name }}</p> -->
        <!--             <p class="text-xs text-gray-400 text-center">{{ (file.size / 1024).toFixed(2) }} KB</p> -->
        <!--         </div> -->
        <!--     </div> -->
        <!-- </div> -->
    </AppLayout>
</template>
