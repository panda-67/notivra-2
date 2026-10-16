<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
  assignment: {
    type: Object,
    required: true,
  },
  group: {
    type: Object,
    required: true,
  },
});

function statusLabel(status) {
  const labels = {
    pending: "Belum dimulai",
    in_progress: "Sedang berlangsung",
    completed: "Selesai",
    invalid: "Tidak valid",
    expired: "Kedaluwarsa",
  };

  return labels[status] ?? status;
}

function statusClass(status) {
  const classes = {
    pending: "bg-slate-100 text-slate-700",
    in_progress: "bg-amber-100 text-amber-800",
    completed: "bg-green-100 text-green-800",
    invalid: "bg-red-100 text-red-800",
    expired: "bg-red-100 text-red-800",
  };

  return classes[status] ?? "bg-slate-100 text-slate-700";
}
</script>

<template>
  <AppLayout title="Detail Kelompok" margin="narrow">
    <Head :title="`Kelompok — ${group.group_name || group.reference_code}`" />

    <div class="p-6 sm:p-8">
      <Link
        :href="route('assignments.groups.index', { slug: assignment.slug })"
        class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-indigo-600"
      >
        &larr; Kembali ke daftar kelompok
      </Link>

      <section class="mt-6 border-b border-slate-200 pb-6">
        <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Detail kelompok</p>

        <h1 class="mt-2 text-2xl font-bold text-slate-900">
          {{ group.group_name || "Kelompok tanpa nama" }}
        </h1>

        <p class="mt-2 text-sm text-slate-600">Tugas: {{ assignment.title }}</p>

        <div class="mt-4 rounded-xl border border-slate-200 p-4">
          <p class="text-sm text-slate-500">Kode referensi</p>

          <p class="mt-1 break-all font-mono text-lg font-bold tracking-wide text-slate-900">
            {{ group.reference_code }}
          </p>
        </div>
      </section>

      <section class="pt-6">
        <div class="mb-5">
          <h2 class="text-lg font-bold text-slate-900">Anggota dan verifikasi</h2>

          <p class="mt-1 text-sm leading-6 text-slate-500">
            Setiap anggota harus menyelesaikan verifikasi masing-masing.
          </p>
        </div>

        <div class="space-y-4">
          <article
            v-for="(member, index) in group.members"
            :key="member.student_number"
            class="rounded-xl border border-slate-200 p-4 sm:p-5"
          >
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
              <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                  Anggota {{ index + 1 }}
                </p>

                <h3 class="mt-1 font-semibold text-slate-900">
                  {{ member.name }}
                </h3>

                <p class="mt-1 text-sm text-slate-500">NIM: {{ member.student_number }}</p>

                <p v-if="member.program_study" class="mt-1 text-sm text-slate-500">
                  {{ member.program_study }}
                </p>
              </div>

              <span
                class="inline-flex w-fit shrink-0 rounded-full px-3 py-1 text-xs font-semibold"
                :class="statusClass(member.status)"
              >
                {{ statusLabel(member.status) }}
              </span>
            </div>
            <div class="mt-4 border-t border-slate-100 pt-4">
              <Link
                v-if="member.start_url"
                :href="member.start_url"
                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700"
              >
                Mulai kuis
              </Link>

              <Link
                v-else-if="member.status === 'in_progress' && member.resume_url"
                :href="member.resume_url"
                class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-700"
              >
                Lanjutkan kuis
              </Link>

              <p v-else-if="member.status === 'completed'" class="text-sm text-green-700">
                Verifikasi telah diselesaikan.
              </p>

              <p v-else-if="member.status === 'in_progress'" class="text-sm text-amber-700">
                Sesi kuis sedang berlangsung, tetapi tautan lanjut belum tersedia.
              </p>

              <p v-else class="text-sm text-slate-500">Tautan mulai kuis tidak tersedia.</p>
            </div>
          </article>
        </div>
      </section>

      <div class="mt-6 rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-600">
        Kelompok baru dapat melanjutkan ke tahap pengunggahan setelah seluruh anggota menyelesaikan
        verifikasi.
      </div>
    </div>
  </AppLayout>
</template>
