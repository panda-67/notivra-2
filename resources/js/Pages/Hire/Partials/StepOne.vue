<script setup>
const props = defineProps({
    services: Array,
    errors: Object,
    user: Object
});

const name = defineModel('name');
const email = defineModel('email');
const phone = defineModel('phone');
const service_id = defineModel('service_id');
const topic = defineModel('topic');
const description = defineModel('description');

</script>

<template>
    <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Layanan <span
                        class="text-red-500">*</span></label>
                <select v-model="service_id"
                    :class="{ 'border-red-300 bg-red-50/30 ring-red-100': errors.service_id, 'border-slate-200': !errors.service_id }"
                    class="w-full rounded-xl border px-2 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all py-3">
                    <option value="">-- Pilih Service --</option>
                    <option v-for="s in services" :key="s.id" :value="s.id">{{ s.title }}</option>
                </select>
                <p v-if="errors.service_id"
                    class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                    {{ errors.service_id }}
                </p>
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Topik Utama <span
                        class="text-red-500">*</span></label>
                <input v-model="topic" type="text" placeholder="Distribusi lebah di ekosistem ..."
                    :class="{ 'border-red-300 bg-red-50/30': errors.topic, 'border-slate-200': !errors.topic }"
                    class="w-full rounded-xl border px-2 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 py-3 transition-all">
                <p v-if="errors.topic" class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                    {{ errors.topic }}</p>
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Lengkap <span
                        class="text-red-500">*</span></label>
                <input v-model="name" type="text" :readonly="!!user"
                    :class="{ 'border-red-300 bg-red-50/30 focus:ring-red-100': errors.name, 'border-slate-200': !errors.name, 'bg-slate-100 text-slate-500 cursor-not-allowed': user }"
                    class="w-full rounded-xl border px-2 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all py-3"
                    placeholder="Masukkan nama lengkap">
                <p v-if="errors.name" class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">{{
                    errors.name }}</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Alamat Email <span
                        class="text-red-500">*</span></label>
                <input v-model="email" type="email" :readonly="!!user"
                    :class="{ 'border-red-300 bg-red-50/30': errors.email, 'border-slate-200': !errors.email, 'bg-slate-100 text-slate-500 cursor-not-allowed': user }"
                    class="w-full rounded-xl border px-2 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all py-3"
                    placeholder="email@contoh.com">
                <p v-if="errors.email" class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                    {{ errors.email }}</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Nomor Handphone </label>
                <input v-model="phone" type="number"
                    :class="{ 'border-red-300 bg-red-50/30': errors.phone, 'border-slate-200': !errors.phone }"
                    class="w-full rounded-xl border px-2 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all py-3"
                    placeholder="0812........">
                <p v-if="errors.phone" class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                    {{ errors.phone }}</p>
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
        </div>
        <div class="mt-4">
            <p v-if="!$page.props.auth.user" class="text-xs text-slate-400 text-left">
                * Anda akan diminta login/register untuk menyimpan detail proyek di tahap berikutnya.
            </p>
        </div>
    </div>
</template>
