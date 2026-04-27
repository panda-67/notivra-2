<script setup>
const props = defineProps({
    errors: Object,
})

const deadline = defineModel('deadline');
const budget = defineModel('budget');
const willing_dp = defineModel('willing_dp');
const address = defineModel('address');
const city = defineModel('city');
const postal_code = defineModel('postal_code');

</script>

<template>
    <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Deadline <span
                        class="text-red-500">*</span></label>
                <input v-model="deadline" type="date"
                    :class="{ 'border-red-300 bg-red-50/30': errors.deadline, 'border-slate-200': !errors.deadline }"
                    class="w-full rounded-xl border px-2 focus:ring-4 focus:ring-blue-100 py-3">
                <p v-if="errors.deadline"
                    class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">{{
                        errors.deadline }}</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Estimasi Anggaran
                    (Rp)</label>
                <input v-model="budget" type="text"
                    :class="{ 'border-red-300 bg-red-50/30': errors.budget, 'border-slate-200': !errors.budget }"
                    class="w-full rounded-xl border px-2 focus:ring-4 focus:ring-blue-100 py-3"
                    placeholder="Contoh: 500000">
                <p v-if="errors.budget" class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">
                    {{ errors.budget }}</p>
            </div>

            <div class="col-span-2">
                <div :class="{ 'border-red-300 bg-red-50/50': errors.willing_dp, 'border-slate-200 bg-slate-50': !errors.willing_dp }"
                    class="p-4 rounded-2xl border transition-all">
                    <label class="flex items-start gap-4 cursor-pointer">
                        <input v-model="willing_dp" type="checkbox"
                            class="mt-1 w-5 h-5 text-blue-600 rounded-md border-slate-300 focus:ring-blue-500">
                        <div>
                            <p class="text-sm font-bold text-slate-900">Konfirmasi Down Payment (DP)</p>
                            <p class="text-xs text-slate-500 mt-0.5 leading-relaxed italic">Saya bersedia
                                melakukan
                                pembayaran awal sesuai kesepakatan untuk memulai pengerjaan.</p>
                        </div>
                    </label>
                </div>
                <p v-if="errors.willing_dp" class="text-red-500 text-xs mt-1.5 font-medium italic">{{
                    errors.willing_dp }}</p>
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Alamat </label>
                <input v-model="address" type="text"
                    :class="{ 'border-red-300 bg-red-50/30 focus:ring-red-100': errors.address, 'border-slate-200': !errors.address }"
                    class="w-full rounded-xl border px-2 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all py-3"
                    placeholder="Masukkan alamat">
                <p v-if="errors.adddress"
                    class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">{{
                        errors.address }}</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Kota </label>
                <input v-model="city" type="text"
                    :class="{ 'border-red-300 bg-red-50/30 focus:ring-red-100': errors.city, 'border-slate-200': !errors.city }"
                    class="w-full rounded-xl border px-2 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all py-3"
                    placeholder="Masukkan kota">
                <p v-if="errors.city" class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">{{
                    errors.city }}</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Kode Pos </label>
                <input v-model="postal_code" type="text"
                    :class="{ 'border-red-300 bg-red-50/30 focus:ring-red-100': errors.postal_code, 'border-slate-200': !errors.postal_code }"
                    class="w-full rounded-xl border px-2 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all py-3"
                    placeholder="Masukkan kode pos">
                <p v-if="errors.postal_code"
                    class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1 italic">{{
                        errors.postal_code }}</p>
            </div>
        </div>
    </div>
</template>
