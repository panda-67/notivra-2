<script setup>
import { computed, reactive } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
  submissions: { type: Object, required: true },
  assignments: { type: Array, default: () => [] },
  filters: { type: Object, required: true },
});

const form = reactive({
  assignment_id: props.filters.assignment_id ?? "",
  duplicate_status: props.filters.duplicate_status ?? "",
  search: props.filters.search ?? "",
});

const rows = computed(() => props.submissions?.data ?? []);

function applyFilters() {
  router.get(
    "/admin/assignments/files",
    {
      assignment_id: form.assignment_id || undefined,
      duplicate_status: form.duplicate_status || undefined,
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
  form.duplicate_status = "";
  form.search = "";
  applyFilters();
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

function formatSize(bytes) {
  if (bytes === null || bytes === undefined) return "—";

  const size = Number(bytes);
  if (!Number.isFinite(size) || size < 0) return "—";
  if (size < 1024) return `${size} B`;
  if (size < 1024 * 1024) return `${(size / 1024).toFixed(1)} KB`;

  return `${(size / (1024 * 1024)).toFixed(2)} MB`;
}

function statusLabel(status) {
  const labels = {
    pending: "Menunggu pemeriksaan",
    clear: "Tidak ditemukan duplikasi",
    flagged: "Terindikasi duplikat",
    under_review: "Perlu tinjauan manual",
    confirmed_duplicate: "Duplikat terkonfirmasi",
  };

  return labels[status] ?? status ?? "Belum diperiksa";
}

function statusClass(status) {
  const classes = {
    pending: "bg-amber-100 text-amber-800",
    clear: "bg-emerald-100 text-emerald-800",
    flagged: "bg-red-100 text-red-800",
    under_review: "bg-orange-100 text-orange-800",
    confirmed_duplicate: "bg-red-200 text-red-900",
  };

  return classes[status] ?? "bg-gray-100 text-gray-700";
}

function membersText(group) {
  if (!group?.members?.length) return "Tidak ada data anggota";

  return group.members
    .map((member) => {
      const nim = member.student_number ? ` (${member.student_number})` : "";
      return `${member.name ?? "Nama tidak tersedia"}${nim}`;
    })
    .join(", ");
}

function metadataEntries(metadata) {
  if (!metadata || typeof metadata !== "object" || Array.isArray(metadata)) {
    return [];
  }

  return Object.entries(metadata).filter(([, value]) => {
    return value !== null && value !== undefined && value !== "";
  });
}
</script>

<template>
  <AdminLayout title="Berkas Submission Assignment">
    <div class="mx-auto w-full max-w-7xl px-4 py-2 sm:px-6 lg:px-8">
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <p class="text-sm text-gray-500">Admin / Assignments / Berkas</p>
          <h1 class="mt-2 text-2xl font-semibold tracking-tight text-gray-900">
            Berkas Submission
          </h1>
          <p class="mt-1 text-sm text-gray-600">
            Daftar dokumen yang dikumpulkan oleh setiap kelompok.
          </p>
        </div>

        <Link
          href="/admin/dashboard"
          class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
          Kembali ke dashboard
        </Link>
      </div>

      <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <section class="rounded-xl border border-gray-200 bg-white p-4">
          <p class="text-sm text-gray-500">Total berkas</p>
          <p class="mt-2 text-2xl font-semibold text-gray-900">
            {{ submissions.total ?? 0 }}
          </p>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-4">
          <p class="text-sm text-gray-500">Berkas pada halaman ini</p>
          <p class="mt-2 text-2xl font-semibold text-gray-900">
            {{ rows.length }}
          </p>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-4">
          <p class="text-sm text-gray-500">Halaman</p>
          <p class="mt-2 text-2xl font-semibold text-gray-900">
            {{ submissions.current_page ?? 1 }}
            <span class="text-base font-normal text-gray-400">
              / {{ submissions.last_page ?? 1 }}
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
            <span class="mb-1.5 block text-sm font-medium text-gray-700"> Status duplikasi </span>
            <select
              v-model="form.duplicate_status"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none"
            >
              <option value="">Semua Status</option>
              <option value="pending">Menunggu Pemeriksaan</option>
              <option value="clear">Tidak Ada Duplikasi</option>
              <option value="flagged">Terindikasi Duplikat</option>
              <option value="under_review">Perlu Tinjauan Manual</option>
              <option value="confirmed_duplicate">Duplikat Terkonfirmasi</option>
            </select>
          </label>

          <label class="block">
            <span class="mb-1.5 block text-sm font-medium text-gray-700"> Pencarian </span>
            <input
              v-model="form.search"
              type="search"
              placeholder="Nama berkas, kelompok, NIM..."
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
          <h2 class="font-semibold text-gray-900">Daftar berkas</h2>
          <span class="text-sm text-gray-500">
            {{ submissions.from ?? 0 }}–{{ submissions.to ?? 0 }} dari
            {{ submissions.total ?? 0 }} berkas
          </span>
        </div>

        <div v-if="!rows.length" class="px-5 py-12 text-center">
          <p class="font-medium text-gray-900">Tidak ada berkas</p>
          <p class="mt-1 text-sm text-gray-500">Belum ada submission yang sesuai dengan filter.</p>
        </div>

        <div v-else class="divide-y divide-gray-200">
          <article v-for="submission in rows" :key="submission.id" class="p-5">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
              <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                  <h3 class="break-all font-semibold text-gray-900">
                    {{ submission.filename || "Dokumen tanpa nama" }}
                  </h3>
                  <span
                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                    :class="statusClass(submission.duplicate_status)"
                  >
                    {{ statusLabel(submission.duplicate_status) }}
                  </span>
                </div>

                <p class="mt-2 text-sm text-gray-600">
                  {{ submission.assignment?.title ?? "Assignment tidak tersedia" }}
                </p>

                <div class="mt-3 grid grid-cols-1 gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
                  <div>
                    <span class="text-gray-500">Kelompok:</span>
                    <span class="ml-1 font-medium text-gray-900">
                      {{ submission.group?.name ?? "—" }}
                    </span>
                  </div>
                  <div>
                    <span class="text-gray-500">Kode referensi:</span>
                    <span class="ml-1 font-mono text-gray-800">
                      {{ submission.group?.reference_code ?? "—" }}
                    </span>
                  </div>
                  <div>
                    <span class="text-gray-500">Ukuran:</span>
                    <span class="ml-1 text-gray-800">
                      {{ formatSize(submission.file_size) }}
                    </span>
                  </div>
                  <div>
                    <span class="text-gray-500">Waktu pengumpulan:</span>
                    <span class="ml-1 text-gray-800">
                      {{ formatDate(submission.submitted_at) }}
                    </span>
                  </div>
                </div>

                <div class="mt-4">
                  <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Anggota kelompok
                  </p>
                  <p class="mt-1 text-sm leading-6 text-gray-700">
                    {{ membersText(submission.group) }}
                  </p>
                </div>

                <details class="mt-4 rounded-lg border border-gray-200">
                  <summary
                    class="cursor-pointer px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                  >
                    Detail teknis dan metadata
                  </summary>
                  <div class="space-y-3 border-t border-gray-200 p-3 text-sm">
                    <div>
                      <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        SHA-256
                      </p>
                      <p class="mt-1 break-all font-mono text-xs text-gray-700">
                        {{ submission.sha256 || "Tidak tersedia" }}
                      </p>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                      <div>
                        <p class="text-xs text-gray-500">Content fingerprint</p>
                        <p class="mt-1 break-all font-mono text-xs text-gray-700">
                          {{ submission.content_fingerprint || "—" }}
                        </p>
                      </div>
                      <div>
                        <p class="text-xs text-gray-500">Structure fingerprint</p>
                        <p class="mt-1 break-all font-mono text-xs text-gray-700">
                          {{ submission.structure_fingerprint || "—" }}
                        </p>
                      </div>
                      <div>
                        <p class="text-xs text-gray-500">Metadata fingerprint</p>
                        <p class="mt-1 break-all font-mono text-xs text-gray-700">
                          {{ submission.metadata_fingerprint || "—" }}
                        </p>
                      </div>
                    </div>

                    <div>
                      <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Metadata dokumen
                      </p>
                      <dl v-if="metadataEntries(submission.metadata).length" class="mt-2 space-y-1">
                        <div
                          v-for="[key, value] in metadataEntries(submission.metadata)"
                          :key="key"
                          class="grid grid-cols-1 gap-1 sm:grid-cols-3"
                        >
                          <dt class="break-words text-gray-500">{{ key }}</dt>
                          <dd class="break-words text-gray-800 sm:col-span-2">
                            {{ typeof value === "object" ? JSON.stringify(value) : value }}
                          </dd>
                        </div>
                      </dl>
                      <p v-else class="mt-1 text-gray-500">Metadata belum tersedia.</p>
                    </div>

                    <div v-if="submission.duplicate_findings?.length">
                      <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Temuan pemeriksaan duplikasi
                      </p>
                      <pre
                        class="mt-2 overflow-x-auto whitespace-pre-wrap break-words rounded-lg bg-gray-50 p-3 text-xs text-gray-700"
                      >
                                                {{
                          JSON.stringify(submission.duplicate_findings, null, 2)
                        }}</pre>
                    </div>
                  </div>
                </details>
              </div>

              <div class="shrink-0">
                <a
                  :href="submission.download_url"
                  class="inline-flex w-full items-center justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 sm:w-auto"
                >
                  Unduh DOCX
                </a>
              </div>
            </div>
          </article>
        </div>

        <div
          v-if="submissions.links?.length > 3"
          class="flex flex-wrap items-center gap-2 border-t border-gray-200 px-5 py-4"
        >
          <template v-for="(link, index) in submissions.links" :key="index">
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
    </div>
  </AdminLayout>
</template>
