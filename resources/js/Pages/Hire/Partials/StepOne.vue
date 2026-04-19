<script setup>
const props = defineProps({
    services: Array,
    errors: Object
});

const name = defineModel('name');
const email = defineModel('email');
const phone = defineModel('phone');
const service_id = defineModel('service_id');

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
                <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Lengkap <span
                        class="text-red-500">*</span></label>
                <input v-model="name" type="text"
                    :class="{ 'border-red-300 bg-red-50/30 focus:ring-red-100': errors.name, 'border-slate-200': !errors.name }"
                    class="w-full rounded-xl border px-2 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all py-3"
                    placeholder="Masukkan nama lengkap">
                <p v-if="errors.name" class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">{{
                    errors.name }}</p>
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

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Alamat Email <span
                        class="text-red-500">*</span></label>
                <input v-model="email" type="email"
                    :class="{ 'border-red-300 bg-red-50/30': errors.email, 'border-slate-200': !errors.email }"
                    class="w-full rounded-xl border px-2 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all py-3"
                    placeholder="email@contoh.com">
                <p v-if="errors.email" class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                    {{ errors.email }}</p>
            </div>
        </div>
    </div>
</template>
