<script setup>
import { computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { route } from '../../../../vendor/tightenco/ziggy/src/js';
import AppLayout from '../../Layouts/AppLayout.vue';
import StepOne from './Partials/StepOne.vue';
import StepTwo from './Partials/StepTwo.vue';
import StepThree from './Partials/StepThree.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
console.log(user.value);

const props = defineProps({
    draft: { type: Object, default: null },
    services: Array,
});

const form = useForm({
    step: props.draft?.step || 1,
    // Step 1: Identitas
    service_id: props.draft?.data?.service_id || '',
    name: user.value?.name || props.draft?.data?.name || '',
    email: user.value?.email || props.draft?.data?.email || '',
    phone: props.draft?.data?.phone || '',

    // Step 2: Detail Proyek
    topic: props.draft?.data?.topic || '',
    output: props.draft?.data?.output || '',
    institution: props.draft?.data?.institution || '',
    level: props.draft?.data?.level || '',
    field: props.draft?.data?.field || '',
    description: props.draft?.data?.description || '',
    files: '',

    // Step 3: Logistik & Budget
    deadline: props.draft?.data?.deadline || '',
    budget: props.draft?.data?.budget || '',
    willing_dp: props.draft?.data?.willing_dp || false,
    address: props.draft?.data?.address || '',
    city: props.draft?.data?.city || '',
    postal_code: props.draft?.data?.postal_code || '',
});

watch(() => props.draft, (newDraft) => {
    if (newDraft) {
        form.step = newDraft.step;

        form.service_id = newDraft.data.service_id || '';
        form.name = user.value?.name || newDraft.data?.name || '';
        form.email = user.value?.email || newDraft.data?.email || '';
        form.phone = newDraft.data?.phone || '';

        form.institution = newDraft.data?.institution || '';
        form.level = newDraft.data?.level || '';
        form.field = newDraft.data?.field || '';
        form.topic = newDraft.data?.topic || '';
        form.description = newDraft.data?.description || '';
        form.files = '';

        form.output = newDraft.data?.output || '';
        form.deadline = newDraft.data?.deadline || '';
        form.budget = newDraft.data?.budget || '';
        form.willing_dp = newDraft.data?.willing_dp || '';
        form.address = newDraft.data?.address || '';
        form.city = newDraft.data?.city || '';
        form.postal_code = newDraft.data?.postal_code || '';
    }
}, { deep: true });

const saveStep = () => {
    if (!props.draft?.id) {
        form.post(route('hire.draft.store'), {
            onSuccess: () => { console.log('Draft created & moved to Step 2'); },
        });
    } else {
        form.transform((data) => ({
            ...data,
            step: form.step,
            next_step: form.step + 1,
        })).put(route('hire.draft.update', { draft: props.draft.id }), {
            forceFormData: true,
            preserveScroll: false,
            onBefore: (request) => {
                // Kita intip isi FormData yang akan dikirim
                console.log('File yang dikirim:', request.data.get('files'));
            },
            onSuccess: () => {
                form.step = props.draft.step;
                console.log('Step updated');
            },
            onError: (e) => { console.log("Errors", e) }
        });
    }
};

const submitFinal = () => {
    form.post(route('hire.draft.submit', { draft: props.draft.id }), {
        onSuccess: () => {
            console.log('Submitted ...');
        },
        onError: (e) => { console.log("Errors", e) }
    });
};

</script>

<template>
    <AppLayout title="Create Project Brief">
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Hire Our Service</h1>
                    <p class="text-slate-500 text-sm mt-1">Lengkapi detail untuk memulai project baru Anda.</p>
                </div>
                <div class="flex items-center gap-2">
                    <div v-for="i in 3" :key="i" class="flex items-center">
                        <div :class="[
                            'w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300',
                            form.step >= i ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'bg-slate-100 text-slate-400'
                        ]">
                            {{ i }}
                        </div>
                        <div v-if="i < 3"
                            :class="['w-8 h-0.5 ml-2 rounded', form.step > i ? 'bg-blue-600' : 'bg-slate-100']"></div>
                    </div>
                </div>
            </div>
        </template>

        <div class="p-8">
            <div v-if="$page.props.errors.message"
                class="mb-6 p-4 bg-red-50 border border-red-100 rounded-xl flex gap-3">
                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-red-700 font-medium">{{ $page.props.errors.message }}</p>
            </div>

            <form @submit.prevent="saveStep">
                <StepOne v-if="form.step === 1" v-model:name="form.name" v-model:topic="form.topic"
                    v-model:email="form.email" v-model:phone=form.phone v-model:service_id="form.service_id"
                    v-model:description="form.description" :services="services" :user="user" :errors="form.errors" />

                <StepTwo v-if="form.step === 2" v-model:output="form.output" v-model:field="form.field"
                    v-model:level="form.level" v-model:institution="form.institution" v-model:files="form.files"
                    :errors="form.errors" />

                <StepThree v-if="form.step === 3" v-model:deadline="form.deadline" v-model:budget="form.budget"
                    v-model:willing_dp="form.willing_dp" v-model:address="form.address" v-model:city="form.city"
                    v-model:postal_code="form.postal_code" :errors="form.errors" />

                <div class="mt-4 pt-2 border-t border-slate-100 flex items-center justify-between">
                    <button v-if="form.step > 1" type="button" @click="form.step--"
                        class="group flex items-center gap-2 text-slate-500 font-semibold hover:text-slate-900 transition-all">
                        <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Kembali
                    </button>
                    <div v-else></div>

                    <div class="flex items-center gap-3">
                        <button v-if="form.step < 3" type="submit" :disabled="form.processing"
                            class="bg-blue-600 text-white px-10 py-3 rounded-full font-bold shadow-xl shadow-blue-100 hover:bg-blue-700 hover:shadow-blue-200 transition-all active:scale-95 disabled:opacity-50">
                            {{ form.processing ? 'Memproses...' : 'Lanjutkan' }}
                        </button>

                        <button v-if="form.step === 3" type="button" @click="submitFinal" :disabled="form.processing"
                            class="bg-green-600 text-white px-10 py-3 rounded-full font-bold shadow-xl shadow-green-100 hover:bg-green-700 hover:shadow-green-200 transition-all active:scale-95 disabled:opacity-50">
                            {{ form.processing ? 'Mengirim...' : 'Kirim Permintaan' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
