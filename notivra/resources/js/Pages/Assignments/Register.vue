<script setup>
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    assignment: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    group_name: "",
    members: [
        {
            student_number: "",
            name: "",
            program_study: "FKIP Matematika",
            institution: "Universitas Syiah Kuala (USK)",
        },
    ],
});

const canAddMember = computed(() => form.members.length < 5);
const canRemoveMember = computed(() => form.members.length > 1);

function addMember() {
    if (!canAddMember.value) return;

    form.members.push({
        student_number: "",
        name: "",
        program_study: "FKIP Matematika",
        institution: "Universitas Syiah Kuala (USK)",
    });
}

function removeMember(index) {
    if (!canRemoveMember.value) return;

    form.members.splice(index, 1);

    // Bersihkan error validasi untuk indeks yang sudah berubah.
    form.clearErrors();
}

function submit() {
    form
        .transform((data) => ({
            ...data,
            group_name: data.group_name.trim() || null,
            members: data.members.map((member) => ({
                student_number: member.student_number.trim(),
                name: member.name.trim(),
                program_study: member.program_study.trim() || null,
                institution: member.institution.trim() || null,
            })),
        }))
        .post(
            route("assignments.register.store", {
                slug: props.assignment.slug,
            }),
            {
                preserveScroll: true,
                onFinish: () => form.transform((data) => data),
            },
        );
}

function formatDate(value) {
    if (!value) return "Tidak ditentukan";

    return new Intl.DateTimeFormat("id-ID", {
        dateStyle: "long",
        timeStyle: "short",
    }).format(new Date(value));
}
</script>

<template>
    <AppLayout title="Pendaftaran Kelompok" margin="narrow">

        <Head :title="`Pendaftaran — ${assignment.title}`" />

        <div class="p-6 sm:p-8">
            <Link :href="route('assignments.groups.index', {
                slug: assignment.slug,
            })
                " class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 transition hover:text-indigo-600">
                <span aria-hidden="true">&larr;</span>
                Daftar kelompok
            </Link>

            <!-- Informasi tugas -->
            <section class="mt-6 border-b border-slate-200 pb-7">
                <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">
                    Pendaftaran tugas
                </p>

                <h1 class="mt-2 text-2xl font-bold leading-tight text-slate-900 sm:text-3xl">
                    {{ assignment.title }}
                </h1>

                <p v-if="assignment.description" class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-600">
                    {{ assignment.description }}
                </p>

                <div v-if="assignment.instructions"
                    class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:p-5">
                    <h2 class="text-sm font-semibold text-slate-900">Petunjuk tugas</h2>

                    <p class="mt-2 whitespace-pre-line text-sm leading-3 text-slate-600">
                        {{ assignment.instructions }}
                    </p>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                            Pendaftaran dibuka
                        </p>

                        <p class="mt-2 text-sm font-semibold text-slate-900">
                            {{ formatDate(assignment.opens_at) }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-200 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Tenggat tugas</p>

                        <p class="mt-2 text-sm font-semibold text-slate-900">
                            {{ formatDate(assignment.due_at) }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Formulir registrasi -->
            <form class="mt-7 space-y-8" @submit.prevent="submit">
                <section>
                    <div class="mb-4">
                        <h2 class="text-lg font-bold text-slate-900">Identitas kelompok</h2>

                        <p class="mt-1 text-sm leading-6 text-slate-500">
                            Tentukan nama kelompok jika diperlukan. Nama kelompok bersifat opsional.
                        </p>
                    </div>

                    <label for="group_name" class="mb-2 block text-sm font-medium text-slate-700">
                        Nama kelompok
                        <span class="font-normal text-slate-400">(opsional)</span>
                    </label>

                    <input id="group_name" v-model="form.group_name" type="text" maxlength="255" autocomplete="off"
                        placeholder="Contoh: Kelompok 01 Unit 01"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                        :class="{
                            'border-red-500 focus:border-red-500 focus:ring-red-100': form.errors.group_name,
                        }" />

                    <p v-if="form.errors.group_name" class="mt-2 text-sm text-red-600">
                        {{ form.errors.group_name }}
                    </p>
                </section>

                <section>
                    <div class="mb-5 flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Data anggota</h2>

                            <p class="mt-1 text-sm leading-6 text-slate-500">
                                Masukkan data setiap mahasiswa. Setiap anggota akan memiliki sesi verifikasi
                                sendiri.
                            </p>
                        </div>

                        <span class="shrink-0 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                            {{ form.members.length }}/5
                        </span>
                    </div>

                    <div v-if="form.errors.members"
                        class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                        {{ form.errors.members }}
                    </div>

                    <div class="space-y-5">
                        <article v-for="(member, index) in form.members" :key="index"
                            class="rounded-xl border border-slate-200 p-4 sm:p-5">
                            <div class="mb-5 flex items-center justify-between gap-3">
                                <div>
                                    <h3 class="font-semibold text-slate-900">Anggota {{ index + 1 }}</h3>

                                    <p class="mt-1 text-xs text-slate-500">Data identitas mahasiswa</p>
                                </div>

                                <button v-if="canRemoveMember" type="button"
                                    class="rounded-lg px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50"
                                    @click="removeMember(index)">
                                    Hapus
                                </button>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <!-- NIM -->
                                <div class="sm:col-span-2">
                                    <label :for="`student_number_${index}`"
                                        class="mb-2 block text-sm font-medium text-slate-700">
                                        Nomor induk mahasiswa (NIM)
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input :id="`student_number_${index}`" v-model="member.student_number" type="text"
                                        maxlength="64" autocomplete="off" required placeholder="Masukkan NIM"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                                        :class="{
                                            'border-red-500 focus:border-red-500 focus:ring-red-100':
                                                form.errors[`members.${index}.student_number`],
                                        }" />

                                    <p v-if="form.errors[`members.${index}.student_number`]"
                                        class="mt-1.5 text-sm text-red-600">
                                        {{ form.errors[`members.${index}.student_number`] }}
                                    </p>
                                </div>

                                <!-- Nama -->
                                <div class="sm:col-span-2">
                                    <label :for="`member_name_${index}`"
                                        class="mb-2 block text-sm font-medium text-slate-700">
                                        Nama lengkap
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input :id="`member_name_${index}`" v-model="member.name" type="text"
                                        maxlength="255" autocomplete="name" required placeholder="Masukkan nama lengkap"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                                        :class="{
                                            'border-red-500 focus:border-red-500 focus:ring-red-100':
                                                form.errors[`members.${index}.name`],
                                        }" />

                                    <p v-if="form.errors[`members.${index}.name`]" class="mt-1.5 text-sm text-red-600">
                                        {{ form.errors[`members.${index}.name`] }}
                                    </p>
                                </div>

                                <!-- Program studi -->
                                <div>
                                    <label :for="`program_study_${index}`"
                                        class="mb-2 block text-sm font-medium text-slate-700">
                                        Program studi
                                    </label>

                                    <input :id="`program_study_${index}`" v-model="member.program_study" type="text"
                                        maxlength="255" autocomplete="organization-title"
                                        placeholder="Nama program studi"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100" />
                                </div>

                                <!-- Institusi -->
                                <div>
                                    <label :for="`institution_${index}`"
                                        class="mb-2 block text-sm font-medium text-slate-700">
                                        Institusi
                                    </label>

                                    <input :id="`institution_${index}`" v-model="member.institution" type="text"
                                        maxlength="255" autocomplete="organization" placeholder="Nama institusi"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100" />
                                </div>
                            </div>
                        </article>
                    </div>

                    <button v-if="canAddMember" type="button"
                        class="mt-4 inline-flex items-center gap-2 rounded-lg border border-dashed border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-700"
                        @click="addMember">
                        <span class="text-lg leading-none" aria-hidden="true">+</span>
                        Tambah anggota
                    </button>

                    <p class="mt-3 text-xs leading-5 text-slate-500">
                        Minimal 1 dan maksimal 5 anggota per kelompok. Nomor mahasiswa yang sama tidak boleh
                        dimasukkan dua kali dalam satu kelompok.
                    </p>
                </section>

                <!-- Error umum -->
                <div v-if="form.errors.assignment"
                    class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    {{ form.errors.assignment }}
                </div>

                <!-- Aksi -->
                <section class="border-t border-slate-200 pt-6">
                    <div class="rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                        Periksa kembali data kelompok sebelum mendaftar. Setelah berhasil, Anda akan diarahkan
                        ke halaman detail kelompok untuk melihat status verifikasi setiap anggota.
                    </div>

                    <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <Link :href="route('assignments.groups.index', {
                            slug: assignment.slug,
                        })
                            " class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Batal
                        </Link>

                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60">
                            <svg v-if="form.processing" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                            </svg>

                            {{ form.processing ? "Mendaftarkan..." : "Daftarkan kelompok" }}
                        </button>
                    </div>
                </section>
            </form>
        </div>
    </AppLayout>
</template>
