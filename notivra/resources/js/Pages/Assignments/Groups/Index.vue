<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    assignment: {
        type: Object,
        required: true,
    },
    groups: {
        type: Object,
        required: true,
    },
});

function formatDate(value) {
    if (!value) return "Tidak ditentukan";

    return new Intl.DateTimeFormat("id-ID", {
        dateStyle: "long",
        timeStyle: "short",
    }).format(new Date(value));
}

function statusLabel(status) {
    const labels = {
        verification_in_progress: "Verifikasi berlangsung",
        ready_for_upload: "Siap mengunggah",
        submitted: "Sudah mengumpulkan",
    };

    return labels[status] ?? status;
}
</script>

<template>
    <AppLayout title="Kelompok Tugas" margin="wide">

        <Head :title="`Kelompok — ${assignment.title}`" />

        <div
            class="p-6 sm:p-8 mx-auto max-w-6xl bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
            <section class="border-b border-slate-200 pb-7">
                <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Tugas</p>

                <h1 class="mt-2 text-2xl font-bold text-slate-900">
                    {{ assignment.title }}
                </h1>

                <p v-if="assignment.description" class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600">
                    {{ assignment.description }}
                </p>

                <div v-if="assignment.instructions" class="mt-5 rounded-xl bg-slate-50 p-4">
                    <h2 class="font-semibold text-slate-900">Petunjuk tugas</h2>
                    <p class="mt-2 whitespace-pre-line text-sm leading-3.5 text-slate-600">
                        {{ assignment.instructions }}
                    </p>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 p-4">
                        <p class="text-sm text-slate-500">Mulai</p>
                        <p class="mt-1 font-medium text-slate-900">
                            {{ formatDate(assignment.opens_at) }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-200 p-4">
                        <p class="text-sm text-slate-500">Tenggat</p>
                        <p class="mt-1 font-medium text-slate-900">
                            {{ formatDate(assignment.due_at) }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <Link :href="route('assignments.register', { slug: assignment.slug })"
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                        Daftarkan kelompok
                    </Link>
                </div>
            </section>

            <section class="pt-7">
                <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Kelompok terdaftar</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Pilih kelompok untuk melihat anggota dan status verifikasi.
                        </p>
                    </div>

                    <span class="text-sm text-slate-500"> {{ groups.total }} kelompok </span>
                </div>

                <div v-if="groups.data.length" class="divide-y divide-slate-200 rounded-xl border border-slate-200">
                    <article v-for="group in groups.data" :key="group.reference_code"
                        class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <h3 class="font-semibold text-slate-900">
                                {{ group.group_name || "Kelompok tanpa nama" }}
                            </h3>

                            <p class="mt-1 break-all font-mono text-xs text-slate-500">
                                {{ group.reference_code }}
                            </p>

                            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-500">
                                <span>{{ group.members_count }} anggota</span>
                                <span>{{ statusLabel(group.status) }}</span>
                            </div>
                        </div>

                        <Link :href="group.detail_url"
                            class="inline-flex shrink-0 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                            Lihat detail
                        </Link>
                    </article>
                </div>

                <div v-else class="rounded-xl border border-dashed border-slate-300 px-5 py-12 text-center">
                    <h3 class="font-semibold text-slate-900">Belum ada kelompok</h3>
                    <p class="mt-2 text-sm text-slate-500">
                        Jadilah kelompok pertama yang mendaftar untuk tugas ini.
                    </p>
                </div>

                <nav v-if="groups.links?.length > 3" class="mt-6 flex flex-wrap gap-2"
                    aria-label="Navigasi halaman kelompok">
                    <template v-for="(link, index) in groups.links" :key="index">
                        <Link v-if="link.url" :href="link.url" preserve-scroll
                            class="rounded-lg border px-3 py-2 text-sm" :class="link.active
                                    ? 'border-indigo-600 bg-indigo-600 text-white'
                                    : 'border-slate-200 text-slate-700 hover:bg-slate-50'
                                ">
                            <span v-html="link.label" />
                        </Link>

                        <span v-else class="rounded-lg border border-slate-100 px-3 py-2 text-sm text-slate-300"
                            v-html="link.label" />
                    </template>
                </nav>
            </section>
        </div>
    </AppLayout>
</template>
