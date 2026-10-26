<script setup>
import { reactive } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
  results: { type: Object, required: true },
  assignments: { type: Array, default: () => [] },
  filters: { type: Object, required: true },
  unmappedGroupNames: { type: Array, default: () => [] },
});

const form = reactive({
  assignment_id: props.filters.assignment_id ?? "",
  unit: props.filters.unit ?? "",
  status: props.filters.status ?? "",
  search: props.filters.search ?? "",
});

const statusOptions = [
  { value: "pending", label: "Menunggu dimulai" },
  { value: "in_progress", label: "Sedang dikerjakan" },
  { value: "completed", label: "Selesai" },
  { value: "expired", label: "Kedaluwarsa" },
  { value: "invalid", label: "Tidak valid" },
];

const statusLabels = Object.fromEntries(statusOptions.map((item) => [item.value, item.label]));

function applyFilters() {
  router.get(
    "/admin/assignments/quiz-results",
    {
      assignment_id: form.assignment_id || undefined,
      unit: form.unit || undefined,
      status: form.status || undefined,
      search: form.search.trim() || undefined,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    },
  );
}

function resetFilters() {
  form.assignment_id = "";
  form.unit = "";
  form.status = "";
  form.search = "";
  applyFilters();
}

function exportUrl() {
  const query = new URLSearchParams();

  if (form.assignment_id) {
    query.set("assignment_id", form.assignment_id);
  }

  const suffix = query.toString();
  return `/admin/assignments/quiz-results/export${suffix ? `?${suffix}` : ""}`;
}

function formatDate(value) {
  if (!value) return "—";

  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return "—";

  return new Intl.DateTimeFormat("id-ID", {
    dateStyle: "medium",
    timeStyle: "short",
  }).format(date);
}

function statusLabel(status) {
  return statusLabels[status] ?? status ?? "Tidak diketahui";
}

function statusClass(status) {
  const classes = {
    pending: "bg-gray-100 text-gray-700",
    in_progress: "bg-blue-100 text-blue-800",
    completed: "bg-emerald-100 text-emerald-800",
    expired: "bg-red-100 text-red-800",
    invalid: "bg-amber-100 text-amber-800",
  };

  return classes[status] ?? "bg-gray-100 text-gray-700";
}

function formatScore(score) {
  if (score === null || score === undefined) return "Belum final";

  return `${Number(score).toLocaleString("id-ID", {
    maximumFractionDigits: 2,
  })}%`;
}
</script>

<template>
  <AdminLayout title="Laporan Hasil Kuis">
    <div class="mx-auto w-full max-w-7xl px-4 py-2 sm:px-6 lg:px-8">
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <p class="text-sm text-gray-500">Admin / Assignments / Laporan kuis</p>
          <h1 class="mt-2 text-2xl font-semibold tracking-tight text-gray-900">
            Laporan Hasil Kuis
          </h1>
          <p class="mt-1 text-sm text-gray-600">
            Hasil per mahasiswa, status pengerjaan, dan skor akhir kuis.
          </p>
        </div>

        <div class="flex flex-wrap gap-2">
          <a
            :href="exportUrl()"
            class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800"
          >
            Ekspor Excel (.xlsx)
          </a>
          <Link
            href="/admin/dashboard"
            class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Dashboard
          </Link>
        </div>
      </div>

      <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <section class="rounded-xl border border-gray-200 bg-white p-4">
          <p class="text-sm text-gray-500">Hasil pada halaman ini</p>
          <p class="mt-2 text-2xl font-semibold text-gray-900">
            {{ results.data?.length ?? 0 }}
          </p>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-4">
          <p class="text-sm text-gray-500">Total hasil sesuai filter</p>
          <p class="mt-2 text-2xl font-semibold text-gray-900">
            {{ results.total ?? 0 }}
          </p>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-4">
          <p class="text-sm text-gray-500">Halaman</p>
          <p class="mt-2 text-2xl font-semibold text-gray-900">
            {{ results.current_page ?? 1 }}
            <span class="text-base font-normal text-gray-400">
              / {{ results.last_page ?? 1 }}
            </span>
          </p>
        </section>
      </div>

      <form
        class="mb-5 rounded-xl border border-gray-200 bg-white p-4"
        @submit.prevent="applyFilters"
      >
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
          <label class="block">
            <span class="mb-1.5 block text-sm font-medium text-gray-700"> Assignment </span>
            <select
              v-model="form.assignment_id"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none"
            >
              <option value="">Semua assignment</option>
              <option
                v-for="assignment in assignments"
                :key="assignment.id"
                :value="String(assignment.id)"
              >
                {{ assignment.title }}
              </option>
            </select>
          </label>

          <label class="block">
            <span class="mb-1.5 block text-sm font-medium text-gray-700"> Unit </span>
            <select
              v-model="form.unit"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none"
            >
              <option value="">Semua unit</option>
              <option value="1">Unit 1</option>
              <option value="2">Unit 2</option>
              <option value="3">Unit 3</option>
            </select>
          </label>

          <label class="block">
            <span class="mb-1.5 block text-sm font-medium text-gray-700"> Status kuis </span>
            <select
              v-model="form.status"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none"
            >
              <option value="">Semua status</option>
              <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </label>

          <label class="block">
            <span class="mb-1.5 block text-sm font-medium text-gray-700"> Pencarian </span>
            <input
              v-model="form.search"
              type="search"
              placeholder="Nama, NIM, kelompok..."
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none"
            />
          </label>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
          <button
            type="submit"
            class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
          >
            Terapkan filter
          </button>
          <button
            type="button"
            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            @click="resetFilters"
          >
            Reset
          </button>
        </div>
      </form>

      <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div
          class="flex flex-col gap-1 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
        >
          <h2 class="font-semibold text-gray-900">Hasil per mahasiswa</h2>
          <span class="text-sm text-gray-500">
            {{ results.from ?? 0 }}–{{ results.to ?? 0 }} dari {{ results.total ?? 0 }} hasil
          </span>
        </div>

        <div v-if="!results.data?.length" class="px-5 py-12 text-center">
          <p class="font-medium text-gray-900">Tidak ada hasil kuis</p>
          <p class="mt-1 text-sm text-gray-500">Belum ada data yang sesuai dengan filter.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th
                  class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                  Mahasiswa
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                  Kelompok / Unit
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                  Status kuis
                </th>
                <th
                  class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                  Benar
                </th>
                <th
                  class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                  Salah
                </th>
                <th
                  class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                  Kosong
                </th>
                <th
                  class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                  Skor
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                  Waktu
                </th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
              <tr
                v-for="result in results.data"
                :key="`${result.assignment_id}-${result.group_id}-${result.student_id}`"
                class="align-top hover:bg-gray-50"
              >
                <td class="px-4 py-4">
                  <p class="font-medium text-gray-900">
                    {{ result.student_name ?? "Nama tidak tersedia" }}
                  </p>
                  <p class="mt-1 text-sm text-gray-600">
                    {{ result.student_number ?? "NIM tidak tersedia" }}
                  </p>
                  <p class="mt-1 text-xs text-gray-500">
                    {{ result.program_study || "Program studi —" }}
                  </p>
                  <p class="mt-2 text-xs text-gray-500">
                    {{ result.assignment_title }}
                  </p>
                </td>

                <td class="px-4 py-4">
                  <p class="font-medium text-gray-900">
                    {{ result.group_name ?? "Kelompok tidak tersedia" }}
                  </p>
                  <p class="mt-1 text-sm text-gray-600">
                    {{ result.unit_label ?? "Tidak terpetakan" }}
                  </p>
                  <p class="mt-1 font-mono text-xs text-gray-500">
                    {{ result.reference_code ?? "—" }}
                  </p>
                </td>

                <td class="px-4 py-4">
                  <span
                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                    :class="statusClass(result.verification_status)"
                  >
                    {{ statusLabel(result.verification_status) }}
                  </span>
                  <p class="mt-2 text-xs text-gray-500">{{ result.total_questions }} soal</p>
                </td>

                <td class="px-4 py-4 text-center text-sm font-medium text-emerald-700">
                  {{ result.correct ?? 0 }}
                </td>
                <td class="px-4 py-4 text-center text-sm font-medium text-red-700">
                  {{ result.incorrect ?? 0 }}
                </td>
                <td class="px-4 py-4 text-center text-sm text-gray-600">
                  {{ result.unanswered ?? 0 }}
                </td>

                <td class="whitespace-nowrap px-4 py-4 text-right">
                  <p
                    class="font-semibold"
                    :class="result.score_is_final ? 'text-gray-900' : 'text-gray-400'"
                  >
                    {{ formatScore(result.score) }}
                  </p>
                  <p v-if="!result.score_is_final" class="mt-1 text-xs text-gray-500">
                    Belum final
                  </p>
                </td>

                <td class="min-w-48 px-4 py-4 text-xs text-gray-600">
                  <p>
                    <span class="font-medium text-gray-700">Mulai:</span>
                    {{ formatDate(result.started_at) }}
                  </p>
                  <p class="mt-1">
                    <span class="font-medium text-gray-700">Batas:</span>
                    {{ formatDate(result.quiz_expires_at) }}
                  </p>
                  <p class="mt-1">
                    <span class="font-medium text-gray-700">Selesai:</span>
                    {{ formatDate(result.completed_at) }}
                  </p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div
          v-if="results.links?.length > 3"
          class="flex flex-wrap items-center gap-2 border-t border-gray-200 px-5 py-4"
        >
          <template v-for="(link, index) in results.links" :key="index">
            <Link
              v-if="link.url"
              :href="link.url"
              preserve-scroll
              class="rounded-lg border px-3 py-2 text-sm"
              :class="
                link.active
                  ? 'border-gray-900 bg-gray-900 text-white'
                  : 'border-gray-300 text-gray-700 hover:bg-gray-50'
              "
            >
              <span v-html="link.label" />
            </Link>
            <span v-else class="rounded-lg border border-gray-100 px-3 py-2 text-sm text-gray-300">
              <span v-html="link.label" />
            </span>
          </template>
        </div>
      </section>

      <section
        v-if="unmappedGroupNames.length"
        class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-5"
      >
        <h2 class="font-semibold text-amber-900">Nama kelompok yang belum terpetakan</h2>
        <p class="mt-1 text-sm text-amber-800">
          Nama berikut tidak cocok dengan pola kelompok dan unit yang dikenali. Periksa penamaan
          kelompok agar laporan dapat dikelompokkan dengan benar.
        </p>
        <ul class="mt-3 list-inside list-disc space-y-1 text-sm text-amber-900">
          <li v-for="name in unmappedGroupNames" :key="name">
            {{ name || "(nama kelompok kosong)" }}
          </li>
        </ul>
      </section>
    </div>
  </AdminLayout>
</template>
