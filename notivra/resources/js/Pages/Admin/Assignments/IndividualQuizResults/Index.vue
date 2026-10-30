<script setup>
import { reactive } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
  results: { type: Object, required: true },
  assignments: { type: Array, default: () => [] },
  filters: { type: Object, required: true },
});

const form = reactive({
  assignment_id: props.filters.assignment_id ?? "",
  status: props.filters.status ?? "",
  search: props.filters.search ?? "",
});

const statusOptions = [
  { value: "pending", label: "Belum dimulai" },
  { value: "in_progress", label: "Sedang dikerjakan" },
  { value: "completed", label: "Selesai" },
  { value: "expired", label: "Kedaluwarsa" },
  { value: "invalid", label: "Tidak valid" },
];

const statusLabels = Object.fromEntries(statusOptions.map((item) => [item.value, item.label]));

function applyFilters() {
  router.get(
    "/admin/assignments/individual-quiz-results",
    {
      assignment_id: form.assignment_id || undefined,
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

  return `/admin/assignments/individual-quiz-results/export${suffix ? `?${suffix}` : ""}`;
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
  if (score === null || score === undefined) {
    return "Belum final";
  }

  return `${Number(score).toLocaleString("id-ID", {
    maximumFractionDigits: 2,
  })}%`;
}
</script>

<template>
  <AdminLayout title="Hasil Kuis Individual">
    <div class="mx-auto w-full max-w-7xl px-4 py-2 sm:px-6 lg:px-8">
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <p class="text-sm text-gray-500">Admin / Assignments / Kuis individual</p>

          <h1 class="mt-2 text-2xl font-semibold tracking-tight text-gray-900">
            Hasil Kuis Individual
          </h1>

          <p class="mt-1 text-sm text-gray-600">
            Hasil peserta berdasarkan nama, NIM, status pengerjaan, dan skor.
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
            href="/admin/assignments/quiz-results"
            class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Hasil Kuis Kelompok
          </Link>

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
          <p class="text-sm text-gray-500">Total sesuai filter</p>
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
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
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
              placeholder="Nama atau NIM..."
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
          <h2 class="font-semibold text-gray-900">Daftar hasil peserta</h2>

          <span class="text-sm text-gray-500">
            {{ results.from ?? 0 }}–{{ results.to ?? 0 }} dari {{ results.total ?? 0 }} peserta
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
                  Peserta
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                  Assignment
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                  Status
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
                  Mulai
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                  Selesai
                </th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-for="row in results.data" :key="row.participant_id" class="hover:bg-gray-50">
                <td class="whitespace-nowrap px-4 py-3">
                  <p class="text-sm font-medium text-gray-900">
                    {{ row.student_name }}
                  </p>
                  <p class="mt-0.5 text-xs text-gray-500">NIM: {{ row.student_number }}</p>
                </td>

                <td class="px-4 py-3 text-sm text-gray-700">
                  {{ row.assignment_title }}
                </td>

                <td class="whitespace-nowrap px-4 py-3">
                  <span
                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                    :class="statusClass(row.quiz_status)"
                  >
                    {{ statusLabel(row.quiz_status) }}
                  </span>
                </td>

                <td class="px-4 py-3 text-center text-sm text-gray-700">
                  {{ row.correct }}
                </td>

                <td class="px-4 py-3 text-center text-sm text-gray-700">
                  {{ row.incorrect }}
                </td>

                <td class="px-4 py-3 text-center text-sm text-gray-700">
                  {{ row.unanswered }}
                </td>

                <td
                  class="whitespace-nowrap px-4 py-3 text-right text-sm font-semibold text-gray-900"
                >
                  {{ formatScore(row.score) }}
                </td>

                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">
                  {{ formatDate(row.started_at) }}
                </td>

                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">
                  {{ formatDate(row.completed_at ?? row.expired_at) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div
          v-if="results.last_page > 1"
          class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 px-5 py-4"
        >
          <p class="text-sm text-gray-500">
            Halaman {{ results.current_page }} dari {{ results.last_page }}
          </p>

          <div class="flex gap-2">
            <button
              type="button"
              :disabled="!results.prev_page_url"
              class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"
              @click="
                router.get(results.prev_page_url, {}, { preserveState: true, preserveScroll: true })
              "
            >
              Sebelumnya
            </button>

            <button
              type="button"
              :disabled="!results.next_page_url"
              class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"
              @click="
                router.get(results.next_page_url, {}, { preserveState: true, preserveScroll: true })
              "
            >
              Berikutnya
            </button>
          </div>
        </div>
      </section>
    </div>
  </AdminLayout>
</template>
