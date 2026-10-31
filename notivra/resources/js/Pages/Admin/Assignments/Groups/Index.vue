<script setup>
import { Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { reactive } from "vue";

const props = defineProps({
  assignment: {
    type: Object,
    required: true,
  },
  groups: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    required: true,
  },
});

const statusLabels = {
  draft: "Draft",
  verification_in_progress: "Verifikasi berlangsung",
  ready_for_upload: "Siap upload",
  submitted: "Sudah dikumpulkan",
  under_review: "Dalam pemeriksaan",
  reviewed: "Selesai diperiksa",
};

const statusClasses = {
  draft: "bg-gray-100 text-gray-700",
  verification_in_progress: "bg-amber-100 text-amber-800",
  ready_for_upload: "bg-emerald-100 text-emerald-800",
  submitted: "bg-blue-100 text-blue-800",
  under_review: "bg-violet-100 text-violet-800",
  reviewed: "bg-gray-100 text-gray-700",
};

const statusOptions = [
  { value: "draft", label: "Draft" },
  {
    value: "verification_in_progress",
    label: "Verifikasi berlangsung",
  },
  {
    value: "ready_for_upload",
    label: "Siap upload",
  },
  {
    value: "submitted",
    label: "Sudah dikumpulkan",
  },
  {
    value: "under_review",
    label: "Dalam pemeriksaan",
  },
  {
    value: "reviewed",
    label: "Selesai diperiksa",
  },
];

const form = reactive({
  status: props.filters.status ?? "",
  search: props.filters.search ?? "",
});

function applyFilters() {
  router.get(
    `/admin/assignments/${encodeURIComponent(props.assignment.slug)}/groups`,
    {
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
  form.status = "";
  form.search = "";

  applyFilters();
}

function formatDate(value) {
  if (!value) return "—";

  return new Intl.DateTimeFormat("id-ID", {
    dateStyle: "medium",
    timeStyle: "short",
  }).format(new Date(value));
}

function statusLabel(status) {
  return statusLabels[status] ?? status ?? "Tidak diketahui";
}

function statusClass(status) {
  return statusClasses[status] ?? "bg-gray-100 text-gray-700";
}

function detailUrl(group) {
  return `/admin/assignments/${encodeURIComponent(props.assignment.slug)}/groups/${encodeURIComponent(group.reference_code)}`;
}
</script>

<template>
  <AdminLayout :title="`Grup Assignment — ${assignment.title}`">
    <div class="mx-auto w-full max-w-7xl px-4 py-2 sm:px-6 lg:px-8">
      <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <p class="text-sm text-gray-500">Admin / Assignments / Grup</p>
          <h1 class="mt-2 text-2xl font-semibold tracking-tight text-gray-900">Grup Assignment</h1>
          <p class="mt-1 text-sm text-gray-600">
            {{ assignment.title }}
          </p>
          <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-sm text-gray-500">
            <span>Status: {{ assignment.status }}</span>
            <span>Dibuka: {{ formatDate(assignment.opens_at) }}</span>
            <span>Tenggat: {{ formatDate(assignment.due_at) }}</span>
          </div>
        </div>

        <Link
          href="/admin/dashboard"
          class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
          Kembali ke dashboard
        </Link>
      </div>

      <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-4">
          <p class="text-sm text-gray-500">Total grup pada halaman ini</p>
          <p class="mt-2 text-2xl font-semibold text-gray-900">
            {{ groups.data?.length ?? 0 }}
          </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4">
          <p class="text-sm text-gray-500">Total grup</p>
          <p class="mt-2 text-2xl font-semibold text-gray-900">
            {{ groups.total ?? 0 }}
          </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4">
          <p class="text-sm text-gray-500">Status assignment</p>
          <p class="mt-2 text-lg font-semibold text-gray-900">
            {{ assignment.status }}
          </p>
        </div>
      </div>

      <form
        class="mb-5 rounded-xl border border-gray-200 bg-white p-4"
        @submit.prevent="applyFilters"
      >
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <label class="block">
            <span class="mb-1.5 block text-sm font-medium text-gray-700"> Status grup </span>

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
              placeholder="Nama kelompok, kode referensi..."
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

      <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
          <h2 class="font-semibold text-gray-900">Daftar grup</h2>
          <span class="text-sm text-gray-500">
            {{ groups.from ?? 0 }}–{{ groups.to ?? 0 }} dari {{ groups.total ?? 0 }}
          </span>
        </div>

        <div v-if="!groups.data?.length" class="px-5 py-12 text-center">
          <p class="font-medium text-gray-900">Belum ada grup</p>
          <p class="mt-1 text-sm text-gray-500">
            Grup yang terdaftar untuk assignment ini akan tampil di sini.
          </p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th
                  class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                  Grup
                </th>
                <th
                  class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                  Anggota
                </th>
                <th
                  class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                  Verifikasi
                </th>
                <th
                  class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                  Status grup
                </th>
                <th
                  class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                  Submission
                </th>
                <th
                  class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                  Aksi
                </th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
              <tr v-for="group in groups.data" :key="group.reference_code" class="hover:bg-gray-50">
                <td class="px-5 py-4">
                  <p class="font-medium text-gray-900">
                    {{ group.group_name }}
                  </p>
                  <p class="mt-1 font-mono text-xs text-gray-500">
                    {{ group.reference_code }}
                  </p>
                  <p
                    v-if="group.upload_bypassed_at"
                    class="mt-1 text-xs font-medium text-amber-700"
                  >
                    Bypass aktif
                  </p>
                </td>

                <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-700">
                  {{ group.members_count }}
                </td>

                <td class="whitespace-nowrap px-5 py-4 text-sm">
                  <span class="text-emerald-700">
                    {{ group.verification_summary?.completed ?? 0 }} selesai
                  </span>
                  <span class="mt-1 block text-amber-700">
                    {{ group.verification_summary?.incomplete ?? 0 }} belum selesai
                  </span>
                </td>

                <td class="whitespace-nowrap px-5 py-4">
                  <span
                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                    :class="statusClass(group.status)"
                  >
                    {{ statusLabel(group.status) }}
                  </span>
                </td>

                <td class="px-5 py-4 text-sm">
                  <template v-if="group.submission">
                    <span class="font-medium text-gray-900">Sudah dikumpulkan</span>
                    <span class="mt-1 block text-xs text-gray-500">
                      {{ formatDate(group.submission.submitted_at) }}
                    </span>
                    <span class="mt-1 block text-xs text-gray-500">
                      Duplikasi: {{ group.submission.duplicate_status ?? "Belum diperiksa" }}
                    </span>
                  </template>
                  <span v-else class="text-gray-500">Belum ada</span>
                </td>

                <td class="whitespace-nowrap px-5 py-4 text-right">
                  <Link
                    :href="detailUrl(group)"
                    class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
                  >
                    Detail grup
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div
          v-if="groups.links?.length > 3"
          class="flex flex-wrap items-center gap-2 border-t border-gray-200 px-5 py-4"
        >
          <template v-for="(link, index) in groups.links" :key="index">
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
      </div>
    </div>
  </AdminLayout>
</template>
