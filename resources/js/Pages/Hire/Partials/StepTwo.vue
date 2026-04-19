<script setup>
const props = defineProps({
    errors: Object
})

const topic = defineModel('topic');
const output = defineModel('output');
const field = defineModel('field');
const level = defineModel('level');
const institution = defineModel('institution');
const description = defineModel('description');
const files = defineModel('files');

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        files.value = file;
    }
};
</script>

<template>
    <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Topik Utama <span
                        class="text-red-500">*</span></label>
                <input v-model="topic" type="text"
                    :class="{ 'border-red-300 bg-red-50/30': errors.topic, 'border-slate-200': !errors.topic }"
                    class="w-full rounded-xl border px-2 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 py-3 transition-all">
                <p v-if="errors.topic" class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                    {{ errors.topic }}</p>
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Luaran <span
                        class="text-red-500">*</span></label>
                <select v-model="output"
                    :class="{ 'border-red-300 bg-red-50/30 ring-red-100': errors.output, 'border-slate-200': !errors.output }"
                    class="w-full rounded-xl border px-2 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all py-3">
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
                <p v-if="errors.output" class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                    {{ errors.output }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Bidang Kajian </label>
                <input v-model="field" type="text"
                    :class="{ 'border-red-300 bg-red-50/30': errors.field, 'border-slate-200': !errors.field }"
                    class="w-full rounded-xl border px-2 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 py-3 transition-all">
                <p v-if="errors.field" class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                    {{ errors.field }}</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Level / Jenjang</label>
                <input v-model="level" type="text"
                    :class="{ 'border-red-300 bg-red-50/30': errors.level, 'border-slate-200': !errors.level }"
                    class="w-full border-slate-200 rounded-xl border px-2 focus:ring-4 focus:ring-blue-100 py-3"
                    placeholder="Contoh: S1 atau S2">
                <p v-if="errors.level" class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                    {{ errors.level }}</p>
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Institusi</label>
                <input v-model="institution" type="text"
                    :class="{ 'border-red-300 bg-red-50/30': errors.institution, 'border-slate-200': !errors.institution }"
                    class="w-full border-slate-200 rounded-xl border px-2 focus:ring-4 focus:ring-blue-100 py-3">
                <p v-if="errors.institution"
                    class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                    {{ errors.institution }}</p>
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi Proyek</label>
                <textarea v-model="description" rows="3"
                    class="w-full border-slate-200 rounded-xl border px-2 focus:ring-4 focus:ring-blue-100 py-3 transition-all"
                    :class="{ 'border-red-300 bg-red-50/30': errors.description, 'border-slate-200': !errors.description }"
                    placeholder="Jelaskan kebutuhan Anda secara detail..."></textarea>
                <p v-if="errors.description"
                    class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                    {{ errors.description }}</p>
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Upload Data Mentah / Dokumen Pendukung
                </label>
                <div :class="{ 'border-red-300 bg-red-50': errors.files, 'border-slate-200 bg-slate-50': !errors.files }"
                    class="relative border-2 border-dashed rounded-2xl p-8 transition-all hover:bg-slate-100 group">

                    <input type="file" @change="handleFileChange"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

                    <div class="text-center">
                        <div
                            class="mx-auto w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-slate-400 group-hover:text-blue-600 transition-colors mb-3">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-slate-900">
                            {{ files ? files.name : 'Klik atau drag file ke sini' }}
                        </p>
                        <p class="text-xs text-slate-500 mt-1">PDF, Excel, atau ZIP (Max. 2MB)</p>
                    </div>
                </div>
                <p v-if="errors.files" class="text-red-500 text-xs mt-2 italic">{{ errors.files }}</p>
            </div>
        </div>
    </div>
</template>
