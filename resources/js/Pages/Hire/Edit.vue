<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    project: Object,
    services: Array,
    statuses: Array
});

const form = useForm({
    service_id: props.project.service_id,
    name: props.project.name,
    email: props.project.email,
    phone: props.project.phone,
    institution: props.project.institution,
    level: props.project.level,
    field: props.project.field,
    topic: props.project.topic,
    description: props.project.description,
    output: props.project.output,
    deadline: props.project.deadline,
    address: props.project.address,
    city: props.project.city,
    postal_code: props.project.postal_code,
    willing_dp: props.project.willing_dp ?? false,
    budget: props.project.budget,
    status: props.project.status,
});

const submit = () => {
    form.put(route('projects.update', props.project.id));
};
</script>

<template>
    <AppLayout title="Edit Project">
        <template #header>
            <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Project</h1>
                        <p class="text-slate-500 text-sm mt-1">
                            Memperbarui detail project <span class="font-bold">{{ project.topic }}</span>
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <Link :href="route('dashboard')"
                            class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-lg transition-all">
                            Batal
                        </Link>
                        <button @click="submit" :disabled="form.processing"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm shadow-blue-200 disabled:opacity-50 transition-all">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto p-6 bg-white shadow-md rounded-xl">
            <form @submit.prevent="submit" class="space-y-6">

                <div class="border-b border-slate-300 pb-6 mb-8">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase mb-4">Informasi Klien</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm mb-1 font-medium">Nama Lengkap</label>
                            <input v-model="form.name" type="text"
                                class="mt-1 py-2 px-2.5 block w-full border-gray-300 rounded-md border focus:border-slate-400" />
                            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm mb-1 font-medium">Email</label>
                            <input v-model="form.email" type="email"
                                class="mt-1 py-2 px-2.5 block w-full border-gray-300 rounded-md border focus:border-slate-400" />
                        </div>
                        <div>
                            <label class="block text-sm mb-1 font-medium">No. WhatsApp/Phone</label>
                            <input v-model="form.phone" type="text"
                                class="mt-1 py-2 px-2.5 block w-full border-gray-300 rounded-md border focus:border-slate-400" />
                        </div>
                        <div>
                            <label class="block text-sm mb-1 font-medium">Institusi</label>
                            <input v-model="form.institution" type="text"
                                class="mt-1 py-2 px-2.5 block w-full border-gray-300 rounded-md border focus:border-slate-400" />
                        </div>
                    </div>
                </div>

                <div class="border-b border-slate-300 pb-6 mb-8">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase mb-4">Detail Project</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm mb-1 font-medium">Layanan</label>
                            <select v-model="form.service_id"
                                class="mt-1 py-2 px-2.5 block w-full border-gray-300 rounded-md border focus:border-slate-400">
                                <option v-for="service in services" :key="service.id" :value="service.id">{{
                                    service.title }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm mb-1 font-medium">Jenjang (Level)</label>
                            <input v-model="form.level" type="text" placeholder="Contoh: S1, S2, Umum"
                                class="mt-1 py-2 px-2.5 block w-full border-gray-300 rounded-md border focus:border-slate-400" />
                        </div>
                        <div>
                            <label class="block text-sm mb-1 font-medium">Bidang (Field)</label>
                            <input v-model="form.field" type="text"
                                class="mt-1 py-2 px-2.5 block w-full border-gray-300 rounded-md border foucs:border-slate-400" />
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm mb-1 font-medium">Topik / Judul</label>
                        <input v-model="form.topic" type="text"
                            class="mt-1 py-2 px-2.5 block w-full border-gray-300 rounded-md border focus:border-slate-400" />
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm mb-1 font-medium">Deskripsi Lengkap</label>
                        <textarea v-model="form.description" rows="3"
                            class="mt-1 py-2 px-2.5 block w-full border-gray-300 rounded-md border focus:border-slate-400"></textarea>
                    </div>
                </div>

                <div class="border-b border-slate-300 pb-6 mb-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm mb-1 font-medium">Output yang Diinginkan</label>
                            <select v-model="form.output"
                                class="mt-1 py-2 px-2.5 block w-full border-gray-300 rounded-md border focus:border-slate-400">
                                <option value="">-- Pilih Luaran --</option>

                                <optgroup label="Akademik & Riset">
                                    <option value="draft_artikel">Draft Artikel/Jurnal</option>
                                    <option value="laporan_penelitian">Laporan Penelitian</option>
                                    <option value="instrumen">Instrumen/Kuesioner</option>
                                </optgroup>

                                <optgroup label="Data & Analisis">
                                    <option value="analisis_statistik">Analisis Statistik & Data</option>
                                    <option value="visualisasi_data">Visualisasi & Infografis</option>
                                    <option value="peta_gis">Pemetaan (GIS)</option>
                                </optgroup>

                                <optgroup label="Lainnya">
                                    <option value="dokumen_strategi">Dokumen Strategi/Policy Brief</option>
                                    <option value="lainnya">Lainnya (Custom)</option>
                                </optgroup>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm mb-1 font-medium">Deadline</label>
                            <input v-model="form.deadline" type="date"
                                class="mt-1 py-2 px-2.5 block w-full border-gray-300 rounded-md border focus:border-slate-400" />
                        </div>
                        <div>
                            <label class="block text-sm mb-1 font-medium">Budget (Rp)</label>
                            <input v-model="form.budget" type="number"
                                class="mt-1 py-2 px-2.5 block w-full border-gray-300 rounded-md border focus:border-slate-400" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div class="md:col-span-1">
                            <label class="block text-sm mb-1 font-medium">Kota</label>
                            <input v-model="form.city" type="text"
                                class="mt-1 py-2 px-2.5 block w-full border-gray-300 rounded-md border focus:border-slate-400" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm mb-1 font-medium">Alamat Lengkap</label>
                            <input v-model="form.address" type="text"
                                class="mt-1 py-2 px-2.5 block w-full border-gray-300 rounded-md border focus:border-slate-400" />
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center">
                        <input id="dp" v-model="form.willing_dp" type="checkbox"
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded" />
                        <label for="dp" class="ml-2 block text-sm mb-1 text-gray-900">Bersedia DP?</label>
                    </div>

                    <div class="w-full md:w-1/3">
                        <label class="block text-sm mb-1 font-medium text-gray-700">Status Project</label>
                        <select v-model="form.status"
                            class="mt-1 py-2 px-2.5 block w-full border-gray-300 rounded-md border focus:border-slate-400 bg-gray-50 font-bold text-blue-700">
                            <option v-for="st in statuses" :key="st" :value="st">{{ st.toUpperCase() }}</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
