<script setup>
import { computed, ref } from "vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
  assignment: {
    type: Object,
    required: true,
  },
  group: {
    type: Object,
    required: true,
  },
});

const MAX_FILE_SIZE = 5 * 1024 * 1024;
const currentStep = ref(1);
const fileInput = ref(null);

const form = useForm({
  file: null,
});

const steps = [
  { number: 1, label: "Pilih berkas" },
  { number: 2, label: "Periksa berkas" },
  { number: 3, label: "Konfirmasi" },
];

const isSubmitted = computed(() => Boolean(props.group.submission));

const canUpload = computed(() => props.group.can_upload === true && !isSubmitted.value);

const selectedFile = computed(() => form.file);

const submissionUrl = computed(() =>
  route("assignments.submission.store", {
    slug: props.assignment.slug,
    reference_code: props.group.reference_code,
  }),
);

const isRefreshing = ref(false);

function refreshStatus() {
  if (isRefreshing.value) return;

  isRefreshing.value = true;

  router.reload({
    only: ["group"],
    onFinish: () => {
      isRefreshing.value = false;
    },
  });
}
function statusLabel(status) {
  const labels = {
    pending: "Belum dimulai",
    in_progress: "Sedang berlangsung",
    completed: "Selesai",
    invalid: "Tidak valid",
    expired: "Kedaluwarsa",
    draft: "Menunggu kesiapan",
    verification_in_progress: "Verifikasi berlangsung",
    ready_for_upload: "Siap mengunggah",
    submitted: "Sudah dikumpulkan",
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
    draft: "bg-slate-100 text-slate-700",
    verification_in_progress: "bg-amber-100 text-amber-800",
    ready_for_upload: "bg-green-100 text-green-800",
    submitted: "bg-indigo-100 text-indigo-800",
  };

  return classes[status] ?? "bg-slate-100 text-slate-700";
}

function formatBytes(bytes) {
  if (typeof bytes !== "number" || !Number.isFinite(bytes) || bytes < 0) {
    return "Ukuran tidak diketahui";
  }

  if (bytes < 1024 * 1024) {
    return `${(bytes / 1024).toFixed(1)} KB`;
  }

  return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
}

function formatDate(value) {
  if (!value) return "Tidak ditentukan";

  const date = new Date(value);

  if (Number.isNaN(date.getTime())) {
    return "Tidak tersedia";
  }

  return new Intl.DateTimeFormat("id-ID", {
    dateStyle: "long",
    timeStyle: "short",
    timeZone: "Asia/Jakarta",
  }).format(date);
}

function handleFileChange(event) {
  const file = event.target.files?.[0] ?? null;

  form.clearErrors("file");
  form.file = null;
  currentStep.value = 1;

  if (!file) return;

  if (!file.name.toLowerCase().endsWith(".docx")) {
    form.setError("file", "Pilih berkas dengan ekstensi .docx.");
    event.target.value = "";
    return;
  }

  if (file.size === 0) {
    form.setError("file", "Berkas kosong tidak dapat diunggah.");
    event.target.value = "";
    return;
  }

  if (file.size > MAX_FILE_SIZE) {
    form.setError("file", "Ukuran berkas melebihi batas maksimum 5 MB.");
    event.target.value = "";
    return;
  }

  form.file = file;
}

function openFilePicker() {
  fileInput.value?.click();
}

function goToStep(step) {
  if (form.processing || isSubmitted.value || !canUpload.value) return;

  if (step < 1 || step > 3) return;

  if (step >= 2 && !selectedFile.value) {
    form.setError("file", "Pilih berkas DOCX terlebih dahulu.");
    currentStep.value = 1;
    return;
  }

  if (step >= 3 && form.errors.file) return;

  currentStep.value = step;
}

function submitAssignment() {
  if (!selectedFile.value || !canUpload.value || form.processing) return;

  form.post(submissionUrl.value, {
    forceFormData: true,
    preserveScroll: true,
    onError: () => {
      currentStep.value = 2;
    },
  });
}
</script>

<template>
  <AppLayout title="Detail Kelompok" margin="wide">
    <Head :title="`Kelompok — ${group.group_name || group.reference_code}`" />

    <div
      class="mx-auto max-w-6xl overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8"
    >
      <Link
        :href="route('assignments.groups.index', { slug: assignment.slug })"
        class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-indigo-600"
      >
        &larr; Kembali ke daftar kelompok
      </Link>

      <!-- Detail kelompok -->
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

        <div class="mt-4 grid gap-3 sm:grid-cols-2">
          <div class="rounded-xl bg-slate-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
              Batas pengumpulan
            </p>
            <p class="mt-1 text-sm font-semibold text-slate-900">
              {{ formatDate(assignment.due_at) }}
            </p>
          </div>

          <div class="rounded-xl bg-slate-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
              Status kelompok
            </p>
            <span
              class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-semibold"
              :class="statusClass(group.status)"
            >
              {{ statusLabel(group.status) }}
            </span>
          </div>
        </div>
      </section>

      <!-- Verifikasi anggota -->
      <section class="pt-6">
        <div class="mb-5">
          <h2 class="text-lg font-bold text-slate-900">Anggota dan verifikasi</h2>
          <p class="mt-1 text-sm leading-6 text-slate-600">
            Periksa status verifikasi seluruh anggota sebelum mengunggah tugas.
          </p>
        </div>

        <div
          v-if="group.members.length"
          class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3"
        >
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

              <p
                v-else-if="member.status === 'completed' || member.status === 'expired'"
                class="text-sm text-green-700"
              >
                Status verifikasi telah ditutup.
              </p>

              <p v-else-if="member.status === 'in_progress'" class="text-sm text-amber-700">
                Sesi kuis sedang berlangsung, tetapi tautan lanjut belum tersedia.
              </p>

              <p v-else class="text-sm text-slate-500">Tautan mulai kuis tidak tersedia.</p>
            </div>
          </article>
        </div>

        <div
          v-else
          class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500"
        >
          Belum ada anggota yang terdaftar pada kelompok ini.
        </div>
      </section>

      <!-- Status pengumpulan -->
      <section class="mt-8 border-t border-slate-200 pt-8">
        <div class="mb-5">
          <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">
            Pengumpulan tugas
          </p>
          <h2 class="mt-1 text-xl font-bold text-slate-900">Unggah dokumen tugas</h2>
          <p class="mt-2 text-sm leading-6 text-slate-600">
            Unggah dokumen final kelompok dalam format DOCX. Pastikan dokumen telah diperiksa
            sebelum dikirim.
          </p>
        </div>

        <!-- Sudah dikumpulkan -->
        <div v-if="isSubmitted" class="rounded-xl border border-green-200 bg-green-50 p-5">
          <div class="flex items-start gap-3">
            <div
              class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-700"
            >
              <span class="text-lg font-bold">✓</span>
            </div>

            <div class="min-w-0">
              <h3 class="font-bold text-green-900">Tugas berhasil dikumpulkan</h3>
              <p class="mt-1 text-sm leading-6 text-green-800">
                Kelompok ini telah memiliki pengumpulan. Formulir unggah dinonaktifkan untuk
                mencegah pengiriman ulang.
              </p>

              <dl class="mt-4 space-y-2 text-sm">
                <div>
                  <dt class="text-green-800">Nama berkas</dt>
                  <dd class="break-all font-semibold text-green-950">
                    {{ group.submission.original_filename }}
                  </dd>
                </div>

                <div v-if="group.submission.file_size != null">
                  <dt class="text-green-800">Ukuran berkas</dt>
                  <dd class="font-semibold text-green-950">
                    {{ formatBytes(group.submission.file_size) }}
                  </dd>
                </div>

                <div v-if="group.submission.submitted_at">
                  <dt class="text-green-800">Waktu pengumpulan</dt>
                  <dd class="font-semibold text-green-950">
                    {{ formatDate(group.submission.submitted_at) }}
                  </dd>
                </div>

                <div v-if="group.submission?.duplicate_status">
                  <div class="flex items-start justify-between gap-4">
                    <div>
                      <div class="text-sm text-green-800">Status pemeriksaan duplikasi</div>

                      <div class="mt-1 font-semibold capitalize text-green-950">
                        {{ group.submission.duplicate_status.replaceAll("_", " ") }}
                      </div>
                    </div>

                    <button
                      type="button"
                      @click="refreshStatus"
                      :disabled="isRefreshing"
                      class="inline-flex cursor-pointer shrink-0 items-center gap-2 rounded-lg border border-green-200 bg-white px-3 py-2 text-sm font-medium text-green-800 transition hover:bg-green-50 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                      <svg
                        v-if="isRefreshing"
                        class="h-4 w-4 animate-spin"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                      >
                        <circle
                          class="opacity-25"
                          cx="12"
                          cy="12"
                          r="10"
                          stroke="currentColor"
                          stroke-width="4"
                        />
                        <path
                          class="opacity-75"
                          fill="currentColor"
                          d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                        />
                      </svg>

                      <svg
                        v-else
                        class="h-4 w-4"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.8"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M4 4v5h5M20 20v-5h-5M5.5 9A7 7 0 0118 6l2 3M18.5 15A7 7 0 016 18l-2-3"
                        />
                      </svg>

                      {{ isRefreshing ? "Memperbarui..." : "Perbarui status" }}
                    </button>
                  </div>

                  <p class="mt-2 text-sm leading-6 text-green-800">
                    Perbarui untuk melihat hasil pemeriksaan terbaru.
                  </p>
                </div>
              </dl>
            </div>
          </div>
        </div>

        <!-- Belum memenuhi syarat -->
        <div v-else-if="!canUpload" class="rounded-xl border border-amber-200 bg-amber-50 p-5">
          <h3 class="font-semibold text-amber-900">Pengunggahan belum tersedia</h3>
          <p class="mt-1 text-sm leading-6 text-amber-800">
            Seluruh anggota harus memenuhi ketentuan verifikasi dan tugas harus masih berada dalam
            periode pengumpulan. Silakan selesaikan proses yang masih tertunda, lalu muat ulang
            halaman ini.
          </p>
        </div>

        <!-- Wizard -->
        <div v-else class="rounded-2xl border border-slate-200 p-4 sm:p-6">
          <!-- Indikator langkah -->
          <ol class="mb-8 grid grid-cols-3 gap-2">
            <li v-for="step in steps" :key="step.number">
              <button
                type="button"
                :disabled="form.processing || step.number > currentStep"
                class="flex w-full cursor-pointer flex-col items-center gap-2 rounded-lg p-2 text-center transition disabled:cursor-default"
                :class="
                  currentStep === step.number
                    ? 'bg-indigo-50 text-indigo-700'
                    : currentStep > step.number
                      ? 'text-green-700'
                      : 'text-slate-400'
                "
                @click="goToStep(step.number)"
              >
                <span
                  class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold"
                  :class="
                    currentStep === step.number
                      ? 'bg-indigo-600 text-white'
                      : currentStep > step.number
                        ? 'bg-green-100 text-green-800'
                        : 'bg-slate-100 text-slate-500'
                  "
                >
                  <span v-if="currentStep > step.number">✓</span>
                  <span v-else>{{ step.number }}</span>
                </span>

                <span class="text-xs font-semibold sm:text-sm">
                  {{ step.label }}
                </span>
              </button>
            </li>
          </ol>

          <!-- Langkah 1 -->
          <div v-if="currentStep === 1">
            <h3 class="text-lg font-bold text-slate-900">1. Pilih dokumen tugas</h3>
            <p class="mt-2 text-sm leading-6 text-slate-600">
              Pilih satu berkas DOCX dengan ukuran maksimum 5 MB.
            </p>

            <input
              ref="fileInput"
              type="file"
              accept=".docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
              class="sr-only"
              :disabled="form.processing"
              @change="handleFileChange"
            />

            <button
              type="button"
              class="mt-5 flex w-full cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 px-5 py-10 text-center transition hover:border-indigo-400 hover:bg-indigo-50/50"
              :disabled="form.processing"
              @click="openFilePicker"
            >
              <span class="text-sm font-semibold text-indigo-700">
                {{ selectedFile ? "Ganti berkas" : "Pilih berkas DOCX" }}
              </span>
              <span class="mt-2 text-xs text-slate-500"> Format .docx · Maksimum 5 MB </span>
            </button>

            <div
              v-if="selectedFile"
              class="mt-4 flex items-start justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4"
            >
              <div class="min-w-0">
                <p class="break-all text-sm font-semibold text-slate-900">
                  {{ selectedFile.name }}
                </p>
                <p class="mt-1 text-xs text-slate-500">
                  {{ formatBytes(selectedFile.size) }}
                </p>
              </div>

              <button
                type="button"
                class="shrink-0 cursor-pointer text-sm font-semibold text-red-600 hover:text-red-700"
                :disabled="form.processing"
                @click="
                  form.file = null;
                  form.clearErrors('file');
                  if (fileInput) fileInput.value = '';
                "
              >
                Hapus
              </button>
            </div>

            <p v-if="form.errors.file" class="mt-3 text-sm text-red-600">
              {{ form.errors.file }}
            </p>

            <div class="mt-6 flex justify-end">
              <button
                type="button"
                class="rounded-lg cursor-pointer bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="!selectedFile || form.processing"
                @click="goToStep(2)"
              >
                Periksa berkas
              </button>
            </div>
          </div>

          <!-- Langkah 2 -->
          <div v-else-if="currentStep === 2">
            <h3 class="text-lg font-bold text-slate-900">2. Periksa dokumen</h3>
            <p class="mt-2 text-sm leading-6 text-slate-600">
              Periksa kembali berkas yang dipilih sebelum melanjutkan.
            </p>

            <dl class="mt-5 divide-y divide-slate-200 rounded-xl border border-slate-200">
              <div class="p-4">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tugas</dt>
                <dd class="mt-1 text-sm font-semibold text-slate-900">
                  {{ assignment.title }}
                </dd>
              </div>

              <div class="p-4">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                  Kelompok
                </dt>
                <dd class="mt-1 text-sm font-semibold text-slate-900">
                  {{ group.group_name || group.reference_code }}
                </dd>
              </div>

              <div class="p-4">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                  Nama berkas
                </dt>
                <dd class="mt-1 break-all text-sm font-semibold text-slate-900">
                  {{ selectedFile?.name }}
                </dd>
              </div>

              <div class="p-4">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Ukuran</dt>
                <dd class="mt-1 text-sm font-semibold text-slate-900">
                  {{ selectedFile ? formatBytes(selectedFile.size) : "-" }}
                </dd>
              </div>

              <div class="p-4">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Format</dt>
                <dd class="mt-1 text-sm font-semibold text-slate-900">DOCX</dd>
              </div>
            </dl>

            <p v-if="form.errors.file" class="mt-3 text-sm text-red-600">
              {{ form.errors.file }}
            </p>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
              <button
                type="button"
                class="rounded-lg cursor-pointer border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                :disabled="form.processing"
                @click="goToStep(1)"
              >
                Kembali
              </button>

              <button
                type="button"
                class="rounded-lg cursor-pointer bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="!selectedFile || form.processing"
                @click="goToStep(3)"
              >
                Lanjut ke konfirmasi
              </button>
            </div>
          </div>

          <!-- Langkah 3 -->
          <div v-else>
            <h3 class="text-lg font-bold text-slate-900">3. Konfirmasi pengumpulan</h3>

            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4">
              <h4 class="font-semibold text-amber-900">Periksa sebelum mengirim</h4>
              <p class="mt-1 text-sm leading-6 text-amber-800">
                Setelah berhasil dikumpulkan, kelompok tidak dapat mengirim ulang melalui formulir
                ini. Pastikan dokumen yang dipilih merupakan versi final.
              </p>
            </div>

            <div class="mt-5 rounded-xl border border-slate-200 p-4">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                Dokumen yang akan dikirim
              </p>
              <p class="mt-2 break-all font-semibold text-slate-900">
                {{ selectedFile?.name }}
              </p>
              <p class="mt-1 text-sm text-slate-500">
                {{ selectedFile ? formatBytes(selectedFile.size) : "-" }}
              </p>
            </div>

            <p v-if="form.errors.file" class="mt-3 text-sm text-red-600">
              {{ form.errors.file }}
            </p>
            <p v-if="form.errors.group" class="mt-3 text-sm text-red-600">
              {{ form.errors.group }}
            </p>
            <p v-if="form.errors.assignment" class="mt-3 text-sm text-red-600">
              {{ form.errors.assignment }}
            </p>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
              <button
                type="button"
                class="rounded-lg cursor-pointer border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-50"
                :disabled="form.processing"
                @click="goToStep(2)"
              >
                Periksa kembali
              </button>

              <button
                type="button"
                class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-lg bg-green-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="!selectedFile || form.processing"
                @click="submitAssignment"
              >
                <span
                  v-if="form.processing"
                  class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"
                />
                {{ form.processing ? "Mengirim berkas..." : "Konfirmasi dan kumpulkan" }}
              </button>
            </div>
          </div>
        </div>
      </section>
    </div>
  </AppLayout>
</template>
