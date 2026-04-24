<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    project: Object,
    services: Array,
});

const isLocked = computed(() =>
    props.project.status === 'processing' || props.project.status === 'final'
);

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
            <div class="mx-auto max-w-7xl px-4 py-2 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Project</h1>
                            <span :class="[
                                'px-2.5 py-0.5 mb-3 rounded-full text-xs font-semibold uppercase',
                                form.status === 'final' ? 'bg-green-100 text-green-700' :
                                    form.status === 'processing' ? 'bg-amber-100 text-amber-700' :
                                        'bg-slate-100 text-slate-600'
                            ]">
                                {{ form.status }}
                            </span>
                        </div>
                        <p class="text-slate-500 text-sm mt-1 truncate">
                            Memperbarui detail project: <span class="font-bold italic ml-0.5 text-slate-700">
                                {{ project.topic }} </span>
                        </p>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
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
                        <div class="md:col-span-2">
                            <label class="block text-slate-500 text-sm mb-1 font-medium">Nama Lengkap</label>
                            <input v-model="form.name" type="text" placeholder="Masukkan nama"
                                :class="{ 'border-red-300 bg-red-50/30': form.errors.name, 'border-slate-200': !form.errors.name }"
                                class="mt-1 py-2 px-2.5 block w-full rounded-md border focus:border-slate-400" />
                            <p v-if="form.errors.name"
                                class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                                {{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-slate-500 text-sm mb-1 font-medium">Email</label>
                            <input v-model="form.email" type="email" placeholder="my@email.com"
                                :class="{ 'border-red-300 bg-red-50/30': form.errors.email, 'border-slate-200': !form.errors.email }"
                                class="mt-1 py-2 px-2.5 block w-full rounded-md border focus:border-slate-400" />
                            <p v-if="form.errors.email"
                                class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                                {{ form.errors.email }}</p>
                        </div>
                        <div>
                            <label class="block text-slate-500 text-sm mb-1 font-medium">No. WhatsApp/Phone</label>
                            <input v-model="form.phone" type="text" placeholder="0812 ......"
                                :class="{ 'border-red-300 bg-red-50/30': form.errors.phone, 'border-slate-200': !form.errors.phone }"
                                class="mt-1 py-2 px-2.5 block w-full rounded-md border focus:border-slate-400" />
                            <p v-if="form.errors.phone"
                                class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                                {{ form.errors.phone }}</p>
                        </div>
                        <div>
                            <label class="block text-slate-500 text-sm mb-1 font-medium">Institusi</label>
                            <input v-model="form.institution" type="text"
                                :class="{ 'border-red-300 bg-red-50/30': form.errors.institution, 'border-slate-200': !form.errors.institution }"
                                class="mt-1 py-2 px-2.5 block w-full rounded-md border focus:border-slate-400" />
                            <p v-if="form.errors.institution"
                                class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                                {{ form.errors.institution }}</p>
                        </div>
                        <div>
                            <label class="block text-slate-500 text-sm mb-1 font-medium">Kota</label>
                            <input v-model="form.city" type="text"
                                :class="{ 'border-red-300 bg-red-50/30': form.errors.city, 'border-slate-200': !form.errors.city }"
                                class="mt-1 py-2 px-2.5 block w-full rounded-md border focus:border-slate-400" />
                            <p v-if="form.errors.city"
                                class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                                {{ form.errors.city }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-slate-500 text-sm mb-1 font-medium">Alamat Lengkap</label>
                            <input v-model="form.address" type="text"
                                :class="{ 'border-red-300 bg-red-50/30': form.errors.address, 'border-slate-200': !form.errors.address }"
                                class="mt-1 py-2 px-2.5 block w-full rounded-md border focus:border-slate-400" />
                            <p v-if="form.errors.address"
                                class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                                {{ form.errors.address }}</p>
                        </div>
                    </div>
                </div>

                <div class="border-b border-slate-300 pb-6 mb-6">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase mb-4">Detail Project</h2>
                    <div v-if="isLocked"
                        class="my-2 bg-amber-50 border border-amber-200 text-amber-700 px-3 py-1 rounded text-xs flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m0 0v2m0-2h2m-2 0H10m11-3V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h7" />
                        </svg>
                        Mode Baca Saja: Project dalam status {{ project.status }}.
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-slate-500 text-sm mb-1 font-medium">Layanan</label>
                            <select v-model="form.service_id" :disabled="isLocked"
                                :class="{ 'bg-gray-100 cursor-not-allowed': isLocked, 'border-red-300 bg-red-50/30': form.errors.service_id, 'border-slate-200': !form.errors.service_id }"
                                class="mt-1 py-2 px-2.5 block w-full rounded-md border focus:border-slate-400">
                                <option value="">-- Pilih Layanan --</option>
                                <option v-for="service in services" :key="service.id" :value="service.id">{{
                                    service.title }}
                                </option>
                            </select>
                            <p v-if="form.errors.service_id"
                                class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                                {{ form.errors.service_id }}</p>
                        </div>
                        <div>
                            <label class="block text-slate-500 text-sm mb-1 font-medium">Jenjang (Level)</label>
                            <input v-model="form.level" type="text" placeholder="Contoh: S1, S2, Umum"
                                :disabled="isLocked"
                                :class="{ 'bg-gray-100 cursor-not-allowed': isLocked, 'border-red-300 bg-red-50/30': form.errors.level, 'border-slate-200': !form.errors.level }"
                                class="mt-1 py-2 px-2.5 block w-full rounded-md border focus:border-slate-400" />
                            <p v-if="form.errors.level"
                                class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                                {{ form.errors.level }}</p>
                        </div>
                        <div>
                            <label class="block text-slate-500 text-sm mb-1 font-medium">Bidang (Field)</label>
                            <input v-model="form.field" type="text" :disabled="isLocked"
                                :class="{ 'bg-gray-100 cursor-not-allowed': isLocked, 'border-red-300 bg-red-50/30': form.errors.field, 'border-slate-200': !form.errors.field }"
                                class="mt-1 py-2 px-2.5 block w-full rounded-md border focus:border-slate-400" />
                            <p v-if="form.errors.field"
                                class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                                {{ form.errors.field }}</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-slate-500 text-sm mb-1 font-medium">Topik / Judul</label>
                        <input v-model="form.topic" type="text" :disabled="isLocked"
                            :class="{ 'bg-gray-100 cursor-not-allowed': isLocked, 'border-red-300 bg-red-50/30': form.errors.topic, 'border-slate-200': !form.errors.topic }"
                            class="mt-1 py-2 px-2.5 block w-full rounded-md border focus:border-slate-400" />
                        <p v-if="form.errors.topic"
                            class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                            {{ form.errors.topic }}</p>
                    </div>

                    <div class="mt-4">
                        <label class="block text-slate-500 text-sm mb-1 font-medium">Deskripsi Lengkap</label>
                        <textarea v-model="form.description" rows="3"
                            :class="{ 'border-red-300 bg-red-50/30': form.errors.description, 'border-slate-200': !form.errors.description }"
                            class="mt-1 py-2 px-2.5 block w-full rounded-md border focus:border-slate-400"></textarea>
                        <p v-if="form.errors.description"
                            class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                            {{ form.errors.description }}</p>
                    </div>
                </div>

                <div class="border-b border-slate-300 pb-6 mb-auto">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-slate-500 text-sm mb-1 font-medium">Output yang Diinginkan</label>
                            <select v-model="form.output" :disabled="isLocked"
                                :class="{ 'bg-gray-100 cursor-not-allowed': isLocked, 'border-red-300 bg-red-50/30': form.errors.output, 'border-slate-200': !form.errors.output }"
                                class="mt-1 py-2 px-2.5 block w-full rounded-md border focus:border-slate-400">
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
                            <p v-if="form.errors.output"
                                class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                                {{ form.errors.output }}</p>
                        </div>
                        <div>
                            <label class="block text-slate-500 text-sm mb-1 font-medium">Deadline</label>
                            <input v-model="form.deadline" type="date" :disabled="isLocked"
                                :class="{ 'bg-gray-100 cursor-not-allowed': isLocked, 'border-red-300 bg-red-50/30': form.errors.deadline, 'border-slate-200': !form.errors.deadline }"
                                class="mt-1 py-2 px-2.5 block w-full rounded-md border focus:border-slate-400" />
                            <p v-if="form.errors.deadline"
                                class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                                {{ form.errors.deadline }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-slate-500 text-sm mb-1 font-medium">Budget (Rp)</label>
                            <input v-model="form.budget" type="number" :disabled="isLocked"
                                :class="{ 'bg-gray-100 cursor-not-allowed': isLocked, 'border-red-300 bg-red-50/30': form.errors.budget, 'border-slate-200': !form.errors.budget }"
                                class="mt-1 py-2 px-2.5 block w-full rounded-md border focus:border-slate-400" />
                            <p v-if="form.errors.budget"
                                class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                                {{ form.errors.budget }}</p>
                        </div>
                        <div class="flex justify-between items-center">
                            <div class="flex items-center mt-6">
                                <input id="dp" v-model="form.willing_dp" type="checkbox" :disabled="isLocked"
                                    :class="{ 'bg-gray-100 cursor-not-allowed': isLocked }"
                                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded" />
                                <label for="dp" class="ml-2 block text-sm mb-1 text-gray-900">Bersedia DP?</label>
                            </div>
                            <button @click="submit" :disabled="form.processing"
                                class="px-3 py-1.5 mt-7 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm shadow-blue-200 disabled:opacity-50 transition-all">
                                {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
