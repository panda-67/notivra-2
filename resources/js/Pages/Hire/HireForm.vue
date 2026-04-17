<script setup>
import { watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { route } from '../../../../vendor/tightenco/ziggy/src/js';

const props = defineProps({
    draft: { type: Object, default: null },
    services: Array,
});

const form = useForm({
    step: props.draft?.step || 1,
    name: props.draft?.data?.name || '',
    service: props.draft?.data?.service || '',
    topic: props.draft?.data?.topic || '',
    deadline: props.draft?.data?.deadline || '',
});

watch(() => props.draft, (newDraft) => {
    if (newDraft) {
        form.step = newDraft.step;
        form.name = newDraft.data?.name || '';
        form.service = newDraft.data?.service || '';
        form.topic = newDraft.data?.topic || '';
        form.deadline = newDraft.data?.deadline || '';
    }
}, { deep: true });

const saveStep = () => {
    if (!props.draft?.id) {
        form.post(route('hire.draft.store'), {
            onSuccess: () => { console.log('Draft created & moved to Step 2'); }
        });
    } else {
        const nextStep = form.step + 1;

        form.transform((data) => ({
            ...data,
            step: form.step,
            next_step: nextStep
        })).put(route('hire.draft.update', { draft: props.draft.id }), {
            preserveScroll: true,
            onSuccess: () => {
                form.step = props.draft.step;
                console.log('Step updated');
            }
        });
    }
};

const submitFinal = () => {
    form.post(route('hire.draft.submit', { draft: props.draft.id }), {
        onSuccess: () => {
            console.log('Submitted ...');
        }
    });
};

</script>


<template>

    <Head>
        <title>Hire</title>
        <meta name="description" content="Hiring progress ...">
    </Head>
    <div class="max-w-2xl mx-auto p-6">
        <div class="flex gap-2 mb-6">
            <div v-for="i in 3" :key="i"
                :class="['h-2 flex-1 rounded', form.step >= i ? 'bg-blue-600' : 'bg-gray-200']">
            </div>
        </div>

        <form @submit.prevent="saveStep">
            <div v-if="form.step === 1">
                <input v-model="form.name" placeholder="Nama" class="block w-full border mb-2 p-2">
                <span v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</span>

                <select v-model="form.service" class="w-full border p-2 rounded">
                    <option value="">-- Pilih Layanan --</option>
                    <option v-for="item in services" :key="item.id" :value="item.id">
                        {{ item.title }} </option>
                </select>
                <span v-if="form.errors.service" class="text-red-500">{{ form.errors.service }}</span>
            </div>

            <div v-else-if="form.step === 2">
                <textarea v-model="form.topic" placeholder="Topik" class="block w-full border mb-2 p-2"></textarea>
                <span v-if="form.errors.topic" class="text-red-500">{{ form.errors.topic }}</span>
            </div>

            <div v-else-if="form.step === 3">
                <input v-model="form.deadline" type="date" class="block w-full border mb-2 p-2">
                <span v-if="form.errors.deadline" class="text-red-500">{{ form.errors.deadline }}</span>
            </div>

            <span v-if="form.errors.debug" class="text-red-500">{{ form.errors.debug }}</span>

            <div class="mt-4 flex items-center">
                <button v-if="form.step > 1" type="button" @click="form.step--"
                    class="bg-gray-500 text-white px-4 py-2 rounded">
                    Kembali
                </button>

                <div class="ml-auto">
                    <button v-if="form.step < 3" type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                        {{ !props.draft?.id ? 'Mulai & Lanjut' : 'Lanjut' }}
                    </button>

                    <button v-if="form.step === 3" type="button" @click="submitFinal" :disabled="form.processing"
                        class="bg-green-600 text-white px-4 py-2 rounded">
                        Kirim Sekarang
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>
