<script setup>
import { computed, reactive, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
  assignment: {
    type: Object,
    required: true,
  },
  participants: {
    type: Array,
    default: () => [],
  },
});

const schedule = reactive({
  opens_at: "",
  due_at: "",
});

const scheduleProcessing = ref(false);
const studentNumber = ref("");
const adding = ref(false);
const processingId = ref(null);
const errorMessage = ref("");
const successMessage = ref("");
const search = ref("");
const showStatusModal = ref(false);
const selectedParticipant = ref(null);
const importFile = ref(null);
const importing = ref(false);
const importInput = ref(null);
const importSummary = ref(null);
const showActionModal = ref(false);
const selectedAction = ref(null);
const actionProcessing = ref(false);

watch(
  () => props.assignment,
  (assignment) => {
    schedule.opens_at = toDatetimeLocal(assignment.opens_at);
    schedule.due_at = toDatetimeLocal(assignment.due_at);
  },
  { immediate: true },
);

const nextParticipantStatus = computed(() => {
  if (!selectedParticipant.value) return null;

  return selectedParticipant.value.status === "registered" ? "disabled" : "registered";
});

const statusActionLabel = computed(() => {
  return nextParticipantStatus.value === "disabled" ? "Nonaktifkan" : "Aktifkan";
});

const filteredParticipants = computed(() => {
  const keyword = search.value.trim().toLowerCase();

  if (!keyword) return props.participants;

  return props.participants.filter((participant) => {
    return [
      participant.student?.student_number,
      participant.student?.name,
      participant.student?.program_study,
    ].some((value) =>
      String(value ?? "")
        .toLowerCase()
        .includes(keyword),
    );
  });
});

const registeredCount = computed(
  () => props.participants.filter((item) => item.status === "registered").length,
);

const disabledCount = computed(
  () => props.participants.filter((item) => item.status === "disabled").length,
);

const startedCount = computed(
  () =>
    props.participants.filter((item) =>
      ["in_progress", "completed", "expired"].includes(item.attempt?.status),
    ).length,
);

const actionConfig = computed(() => {
  if (selectedAction.value === "reset") {
    return {
      title: "Reset Session Kuis",
      button: "Reset Session",
      description:
        "Seluruh jawaban dan snapshot soal pada sesi ini akan dihapus. Peserta harus memulai kuis dari awal.",
      endpoint: "admin.assignments.individual-quiz.participants.session.reset",
      method: "POST",
      buttonClass: "bg-red-600 hover:bg-red-700",
    };
  }

  if (selectedAction.value === "device") {
    return {
      title: "Hapus Ikatan Perangkat",
      button: "Hapus Device",
      description:
        "Ikatan perangkat dan resume token lama akan dicabut. Jawaban serta sisa waktu kuis tetap dipertahankan.",
      endpoint: "admin.assignments.individual-quiz.participants.device.remove",
      method: "DELETE",
      buttonClass: "bg-amber-600 hover:bg-amber-700",
    };
  }

  return null;
});

function openActionModal(participant, action) {
  if (actionProcessing.value || processingId.value !== null) return;

  selectedParticipant.value = participant;
  selectedAction.value = action;
  showActionModal.value = true;
}

function closeActionModal() {
  if (actionProcessing.value) return;

  showActionModal.value = false;
  selectedAction.value = null;
  selectedParticipant.value = null;
}

function toDatetimeLocal(value) {
  if (!value) return "";

  const date = new Date(value);

  if (Number.isNaN(date.getTime())) return "";

  const offset = date.getTimezoneOffset() * 60000;

  return new Date(date.getTime() - offset).toISOString().slice(0, 16);
}

async function executeParticipantAction() {
  const participant = selectedParticipant.value;
  const config = actionConfig.value;

  if (!participant || !config || actionProcessing.value) return;

  actionProcessing.value = true;
  clearNotification("error");
  clearNotification("success");

  try {
    const response = await request(
      route(config.endpoint, {
        slug: props.assignment.slug,
        participant: participant.id,
      }),
      {
        method: config.method,
      },
    );

    showActionModal.value = false;
    selectedAction.value = null;
    selectedParticipant.value = null;

    notify("success", response.message);

    router.reload({
      only: ["participants"],
      preserveScroll: true,
    });
  } catch (error) {
    notify("error", error.message || "Operasi gagal diproses.");
  } finally {
    actionProcessing.value = false;
  }
}

function notify(type, message) {
  errorMessage.value = "";
  successMessage.value = "";

  if (type === "error") {
    errorMessage.value = message;
  } else {
    successMessage.value = message;
  }
}

function clearNotification(type) {
  if (type === "error") errorMessage.value = "";
  else successMessage.value = "";
}

function csrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.content ?? "";
}

async function addParticipant() {
  const number = studentNumber.value.trim();

  if (!number || adding.value) return;

  adding.value = true;
  clearNotification("error");
  clearNotification("success");

  try {
    const response = await request(
      route("admin.assignments.individual-quiz.participants.store", {
        slug: props.assignment.slug,
      }),
      {
        method: "POST",
        body: JSON.stringify({
          student_number: number,
        }),
      },
    );

    notify("success", response.message || "Peserta berhasil didaftarkan.");

    studentNumber.value = "";

    router.reload({
      only: ["participants"],
      preserveScroll: true,
    });
  } catch (error) {
    notify("error", error.message || "Peserta gagal didaftarkan.");
  } finally {
    adding.value = false;
  }
}

function openStatusModal(participant) {
  if (processingId.value !== null) return;

  selectedParticipant.value = participant;
  showStatusModal.value = true;
}

function closeStatusModal() {
  if (processingId.value !== null) return;

  showStatusModal.value = false;
  selectedParticipant.value = null;
}

async function toggleStatus() {
  const participant = selectedParticipant.value;

  if (!participant || processingId.value !== null) return;

  const nextStatus = nextParticipantStatus.value;

  processingId.value = participant.id;
  clearNotification("error");
  clearNotification("success");

  try {
    const response = await request(
      route("admin.assignments.individual-quiz.participants.status.update", {
        slug: props.assignment.slug,
        participant: participant.id,
      }),
      {
        method: "PATCH",
        body: JSON.stringify({
          status: nextStatus,
        }),
      },
    );

    showStatusModal.value = false;
    selectedParticipant.value = null;

    notify("success", response.message || "Status peserta berhasil diperbarui.");

    router.reload({
      only: ["participants"],
      preserveScroll: true,
    });
  } catch (error) {
    notify("error", error.message || "Status peserta gagal diperbarui.");
  } finally {
    processingId.value = null;
  }
}

function formatDate(value) {
  if (!value) return "—";

  return new Intl.DateTimeFormat("id-ID", {
    dateStyle: "medium",
    timeStyle: "short",
  }).format(new Date(value));
}

function attemptLabel(status) {
  const labels = {
    pending: "Belum dimulai",
    in_progress: "Sedang mengerjakan",
    completed: "Selesai",
    expired: "Waktu habis",
    invalid: "Tidak valid",
  };

  return labels[status] || "Belum ada sesi";
}

function attemptClass(status) {
  const classes = {
    pending: "bg-gray-100 text-gray-700",
    in_progress: "bg-blue-100 text-blue-700",
    completed: "bg-green-100 text-green-700",
    expired: "bg-amber-100 text-amber-700",
    invalid: "bg-red-100 text-red-700",
  };

  return classes[status] || "bg-gray-100 text-gray-500";
}

async function request(url, options = {}) {
  const isFormData = options.body instanceof FormData;

  const headers = {
    Accept: "application/json",
    "X-Requested-With": "XMLHttpRequest",
    "X-CSRF-TOKEN": csrfToken(),
    ...options.headers,
  };

  if (!isFormData) {
    headers["Content-Type"] = "application/json";
  }

  const response = await fetch(url, {
    ...options,
    headers,
  });

  const data = await response.json().catch(() => ({}));

  if (!response.ok) {
    const validationMessage = Object.values(data.errors ?? {})
      .flat()
      .find(Boolean);

    throw new Error(
      validationMessage || data.message || "Terjadi kesalahan saat memproses permintaan.",
    );
  }

  return data;
}

function selectImportFile(event) {
  const file = event.target.files?.[0] ?? null;

  importFile.value = file;
  importSummary.value = null;
  clearNotification("error");

  if (!file) return;

  const allowedExtensions = ["xlsx", "xls", "csv"];
  const extension = file.name.split(".").pop()?.toLowerCase();

  if (!allowedExtensions.includes(extension)) {
    importFile.value = null;
    event.target.value = "";

    notify("error", "Format file harus XLSX, XLS, atau CSV.");
    return;
  }

  if (file.size > 10 * 1024 * 1024) {
    importFile.value = null;
    event.target.value = "";

    notify("error", "Ukuran file maksimal 10 MB.");
  }
}

async function importParticipants() {
  if (!importFile.value || importing.value) return;

  importing.value = true;
  importSummary.value = null;
  clearNotification("error");
  clearNotification("success");

  try {
    const formData = new FormData();
    formData.append("file", importFile.value);

    const response = await request(
      route("admin.assignments.individual-quiz.participants.import", {
        slug: props.assignment.slug,
      }),
      {
        method: "POST",
        body: formData,
      },
    );

    importSummary.value = response.summary;

    const summary = response.summary;

    if (summary.failed > 0) {
      notify(
        "error",
        `Import selesai dengan ${summary.failed} baris gagal. Periksa rincian hasil import.`,
      );
    } else {
      notify(
        "success",
        `Import selesai. ${summary.new_participants} peserta baru berhasil didaftarkan.`,
      );
    }

    importFile.value = null;

    if (importInput.value) {
      importInput.value.value = "";
    }

    router.reload({
      only: ["participants"],
      preserveScroll: true,
    });
  } catch (error) {
    notify("error", error.message || "Import peserta gagal diproses.");
  } finally {
    importing.value = false;
  }
}

async function updateSchedule() {
  if (!schedule.opens_at || !schedule.due_at) {
    notify("error", "Waktu mulai dan berakhir wajib diisi.");
    return;
  }

  if (new Date(schedule.due_at) <= new Date(schedule.opens_at)) {
    notify("error", "Waktu berakhir harus setelah waktu mulai.");
    return;
  }

  scheduleProcessing.value = true;

  try {
    const response = await request(
      route(
        "admin.assignments.individual-quiz.participants.schedule.update",
        props.assignment.slug,
      ),
      {
        method: "PATCH",
        body: JSON.stringify({
          opens_at: schedule.opens_at,
          due_at: schedule.due_at,
        }),
      },
    );

    notify("success", response.message);

    router.reload({
      only: ["assignment"],
      preserveScroll: true,
    });
  } catch (error) {
    notify("error", error.message);
  } finally {
    scheduleProcessing.value = false;
  }
}
</script>

<template>
  <AdminLayout title="Peserta Kuis Individual">
    <div class="mx-auto w-full max-w-7xl px-4 py-2 sm:px-6 lg:px-8 space-y-5">
      <!-- Header -->
      <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div>
          <div class="mb-1 text-sm text-gray-500">
            Admin / Assignments / Individual Quiz / Participants
          </div>

          <h1 class="mt-2 text-2xl font-semibold tracking-tight text-gray-900">
            Peserta Individual Quiz
          </h1>

          <p class="mt-1 text-sm text-gray-500">
            {{ assignment.title }}
          </p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-600">
          Durasi kuis:
          <span class="font-semibold text-gray-900"> {{ assignment.duration_minutes }} menit </span>
        </div>
      </div>

      <!-- Update schedule -->
      <section class="rounded-xl border border-gray-200 bg-white p-4">
        <div class="mb-4 flex items-center justify-between">
          <div>
            <h2 class="text-sm font-semibold text-gray-900">Jadwal pelaksanaan kuis</h2>
            <p class="mt-1 text-xs text-gray-500">
              Atur periode akses peserta untuk mengerjakan kuis.
            </p>
          </div>

          <span class="rounded-md bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
            Asia/Jakarta (WIB)
          </span>
        </div>

        <form
          class="grid grid-cols-1 items-end gap-3 md:grid-cols-[1fr_1fr_auto]"
          @submit.prevent="updateSchedule"
        >
          <div>
            <label class="mb-1.5 block text-xs font-medium text-gray-700"> Waktu mulai </label>

            <input
              v-model="schedule.opens_at"
              type="datetime-local"
              required
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
            />
          </div>

          <div>
            <label class="mb-1.5 block text-xs font-medium text-gray-700"> Waktu berakhir </label>

            <input
              v-model="schedule.due_at"
              type="datetime-local"
              required
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
            />
          </div>

          <button
            type="submit"
            :disabled="scheduleProcessing"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
          >
            <icon
              v-if="scheduleProcessing"
              name="loader-circle"
              class="mr-2 h-4 w-4 animate-spin"
            />

            {{ scheduleProcessing ? "Menyimpan..." : "Simpan jadwal" }}
          </button>
        </form>

        <p class="mt-3 text-xs text-gray-500">
          Durasi pengerjaan setiap peserta tetap
          {{ assignment.duration_minutes }} menit dan tidak berubah ketika jadwal diperbarui.
        </p>
      </section>

      <!-- Statistics -->
      <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-3">
          <p class="text-xs text-gray-500">Total peserta</p>
          <p class="mt-1 text-2xl font-bold text-gray-900">
            {{ participants.length }}
          </p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-3">
          <p class="text-xs text-gray-500">Terdaftar</p>
          <p class="mt-1 text-2xl font-bold text-green-700">
            {{ registeredCount }}
          </p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-3">
          <p class="text-xs text-gray-500">Dinonaktifkan</p>
          <p class="mt-1 text-2xl font-bold text-red-700">
            {{ disabledCount }}
          </p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-3">
          <p class="text-xs text-gray-500">Sudah memulai sesi</p>
          <p class="mt-1 text-2xl font-bold text-blue-700">
            {{ startedCount }}
          </p>
        </div>
      </div>

      <!-- Add participant -->
      <section class="rounded-lg border border-gray-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-gray-900">Tambahkan peserta</h2>

        <p class="mt-1 text-xs text-gray-500">
          Masukkan NIM mahasiswa yang sudah terdaftar pada tabel students.
        </p>

        <form class="mt-3 flex flex-col gap-2 sm:flex-row" @submit.prevent="addParticipant">
          <input
            v-model="studentNumber"
            type="text"
            maxlength="100"
            required
            placeholder="Masukkan NIM mahasiswa"
            class="min-w-0 flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
          />

          <button
            type="submit"
            :disabled="adding || !studentNumber.trim()"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
          >
            <span v-if="adding">Mendaftarkan...</span>
            <span v-else>Tambahkan peserta</span>
          </button>
        </form>
      </section>

      <!-- Import participants -->
      <section class="rounded-lg border border-gray-200 bg-white p-4">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
          <div>
            <h2 class="text-sm font-semibold text-gray-900">Import peserta dari Excel</h2>

            <p class="mt-1 text-xs text-gray-500">
              Daftarkan beberapa mahasiswa sekaligus menggunakan file Excel atau CSV.
            </p>
          </div>

          <a
            :href="
              route('admin.assignments.individual-quiz.participants.template', {
                slug: assignment.slug,
              })
            "
            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-100"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-4 w-4"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M12 3v12" />
              <path d="m7 10 5 5 5-5" />
              <path d="M5 19h14" />
            </svg>

            Unduh template
          </a>
        </div>

        <div class="mt-4 grid gap-3 sm:grid-cols-[1fr_auto] sm:items-center">
          <div>
            <label
              for="participant-import-file"
              class="mb-1.5 block text-xs font-medium text-gray-700"
            >
              Pilih file peserta
            </label>

            <input
              id="participant-import-file"
              ref="importInput"
              type="file"
              accept=".xlsx,.xls,.csv"
              :disabled="importing"
              class="block w-full cursor-pointer rounded-lg border border-gray-300 bg-white text-sm text-gray-600 file:mr-3 file:cursor-pointer file:border-0 file:border-r file:border-gray-300 file:bg-gray-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-gray-700 hover:file:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-50"
              @change="selectImportFile"
            />

            <p class="mt-1.5 text-xs text-gray-400">XLSX, XLS, atau CSV. Maksimal 10 MB.</p>
          </div>

          <button
            type="button"
            :disabled="!importFile || importing"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
            @click="importParticipants"
          >
            <svg
              v-if="importing"
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

            <span v-if="importing">Memproses import...</span>
            <span v-else>Import peserta</span>
          </button>
        </div>

        <div
          v-if="importFile"
          class="mt-3 flex items-center justify-between gap-3 rounded-lg bg-gray-50 px-3 py-2"
        >
          <div class="flex min-w-0 items-center gap-2">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5 shrink-0 text-green-600"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
              <path d="M14 2v6h6" />
            </svg>

            <span class="truncate text-xs font-medium text-gray-700">
              {{ importFile.name }}
            </span>
          </div>

          <span class="shrink-0 text-xs text-gray-400">
            {{ (importFile.size / 1024 / 1024).toFixed(2) }} MB
          </span>
        </div>

        <div class="mt-3 rounded-lg border border-blue-100 bg-blue-50 px-3 py-2.5">
          <p class="text-xs font-semibold text-blue-800">Ketentuan import</p>

          <ul class="mt-1 list-inside list-disc space-y-1 text-xs text-blue-700">
            <li>Gunakan kolom student_number, name, dan program_study.</li>
            <li>Mahasiswa baru akan dibuat otomatis pada tabel students.</li>
            <li>Data mahasiswa yang sudah ada tidak akan ditimpa.</li>
            <li>Mahasiswa inactive akan ditolak sebagai gagal.</li>
            <li>Peserta yang sudah terdaftar atau disabled tidak diubah.</li>
          </ul>
        </div>
      </section>

      <!-- Import result -->
      <section
        v-if="importSummary"
        class="overflow-hidden rounded-lg border border-gray-200 bg-white"
      >
        <div class="border-b border-gray-200 px-4 py-3">
          <div class="flex items-center justify-between gap-3">
            <div>
              <h2 class="text-sm font-semibold text-gray-900">Hasil import peserta</h2>

              <p class="mt-1 text-xs text-gray-500">Ringkasan pemrosesan file Excel.</p>
            </div>

            <span
              class="rounded-full px-2.5 py-1 text-xs font-semibold"
              :class="
                importSummary.failed > 0
                  ? 'bg-amber-100 text-amber-700'
                  : 'bg-green-100 text-green-700'
              "
            >
              {{ importSummary.failed > 0 ? "Selesai dengan kendala" : "Berhasil" }}
            </span>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3 p-4 sm:grid-cols-3 lg:grid-cols-5">
          <div class="rounded-lg bg-gray-50 p-3">
            <p class="text-xs text-gray-500">Diproses</p>
            <p class="mt-1 text-xl font-bold text-gray-900">
              {{ importSummary.processed }}
            </p>
          </div>

          <div class="rounded-lg bg-green-50 p-3">
            <p class="text-xs text-green-700">Mahasiswa baru</p>
            <p class="mt-1 text-xl font-bold text-green-800">
              {{ importSummary.new_students }}
            </p>
          </div>

          <div class="rounded-lg bg-blue-50 p-3">
            <p class="text-xs text-blue-700">Peserta baru</p>
            <p class="mt-1 text-xl font-bold text-blue-800">
              {{ importSummary.new_participants }}
            </p>
          </div>

          <div class="rounded-lg bg-gray-100 p-3">
            <p class="text-xs text-gray-600">Dilewati</p>
            <p class="mt-1 text-xl font-bold text-gray-800">
              {{ importSummary.skipped }}
            </p>
          </div>

          <div class="rounded-lg bg-red-50 p-3">
            <p class="text-xs text-red-700">Gagal</p>
            <p class="mt-1 text-xl font-bold text-red-800">
              {{ importSummary.failed }}
            </p>
          </div>
        </div>

        <div v-if="importSummary.failures?.length" class="border-t border-gray-200">
          <div class="px-4 py-3">
            <h3 class="text-xs font-semibold text-red-700">Rincian baris gagal</h3>

            <p class="mt-1 text-xs text-gray-500">
              Perbaiki data berikut pada file Excel sebelum mengimpornya kembali.
            </p>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full min-w-[600px] text-left text-xs">
              <thead class="bg-red-50 text-red-800">
                <tr>
                  <th class="px-4 py-2 font-semibold">Baris Excel</th>
                  <th class="px-4 py-2 font-semibold">NIM</th>
                  <th class="px-4 py-2 font-semibold">Keterangan</th>
                </tr>
              </thead>

              <tbody class="divide-y divide-gray-100">
                <tr
                  v-for="(failure, index) in importSummary.failures"
                  :key="`${failure.row}-${index}`"
                >
                  <td class="whitespace-nowrap px-4 py-2.5 font-medium text-gray-700">
                    {{ failure.row }}
                  </td>

                  <td class="whitespace-nowrap px-4 py-2.5 text-gray-600">
                    {{ failure.student_number || "—" }}
                  </td>

                  <td class="px-4 py-2.5 text-red-700">
                    {{ failure.reason }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- Participant table -->
      <section class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <div
          class="flex flex-col gap-3 border-b border-gray-200 p-4 sm:flex-row sm:items-center sm:justify-between"
        >
          <div>
            <h2 class="text-sm font-semibold text-gray-900">Daftar peserta</h2>
            <p class="mt-1 text-xs text-gray-500">
              {{ filteredParticipants.length }} peserta ditampilkan
            </p>
          </div>

          <input
            v-model="search"
            type="search"
            placeholder="Cari NIM, nama, atau program studi..."
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 sm:max-w-xs"
          />
        </div>

        <div class="overflow-x-auto">
          <table class="w-full min-w-[850px] text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
              <tr>
                <th class="px-4 py-3 font-semibold">Mahasiswa</th>
                <th class="px-4 py-3 font-semibold">Program studi</th>
                <th class="px-4 py-3 font-semibold">Pendaftaran</th>
                <th class="px-4 py-3 font-semibold">Status kuis</th>
                <th class="px-4 py-3 font-semibold">Waktu mulai</th>
                <th class="px-4 py-3 text-right font-semibold">Aksi</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
              <tr
                v-for="participant in filteredParticipants"
                :key="participant.id"
                class="hover:bg-gray-50"
              >
                <td class="px-4 py-3">
                  <p class="font-medium text-gray-900">
                    {{ participant.student.name }}
                  </p>
                  <p class="mt-0.5 text-xs text-gray-500">
                    {{ participant.student.student_number }}
                  </p>
                </td>

                <td class="px-4 py-3 text-gray-600">
                  {{ participant.student.program_study || "—" }}
                </td>

                <td class="px-4 py-3">
                  <span
                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                    :class="
                      participant.status === 'registered'
                        ? 'bg-green-100 text-green-700'
                        : 'bg-red-100 text-red-700'
                    "
                  >
                    {{ participant.status === "registered" ? "Aktif" : "Nonaktif" }}
                  </span>
                </td>

                <td class="px-4 py-3">
                  <span
                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                    :class="attemptClass(participant.attempt?.status)"
                  >
                    {{ attemptLabel(participant.attempt?.status) }}
                  </span>
                  <p
                    v-if="participant.attempt"
                    class="mt-1 text-xs"
                    :class="participant.attempt.device_bound ? 'text-green-600' : 'text-amber-600'"
                  >
                    {{
                      participant.attempt.device_bound ? "Device terikat" : "Device belum terikat"
                    }}
                  </p>
                  <p v-if="participant.attempt?.completed_at" class="mt-1 text-xs text-gray-400">
                    {{ formatDate(participant.attempt.completed_at) }}
                  </p>
                </td>

                <td class="px-4 py-3 text-xs text-gray-600">
                  {{ formatDate(participant.attempt?.started_at) }}
                </td>

                <td class="px-4 py-3">
                  <div class="flex flex-wrap justify-end gap-1.5">
                    <button
                      v-if="participant.attempt"
                      type="button"
                      class="rounded-lg border border-amber-200 px-3 py-2 text-xs font-medium text-amber-700 hover:bg-amber-50"
                      @click="openActionModal(participant, 'device')"
                    >
                      Hapus Device
                    </button>

                    <button
                      v-if="participant.attempt"
                      type="button"
                      class="rounded-lg border border-red-200 px-3 py-2 text-xs font-medium text-red-700 hover:bg-red-50"
                      @click="openActionModal(participant, 'reset')"
                    >
                      Reset Session
                    </button>

                    <button
                      type="button"
                      class="rounded-lg border px-3 py-2 text-xs font-medium"
                      :class="
                        participant.status === 'registered'
                          ? 'border-red-200 text-red-700 hover:bg-red-50'
                          : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50'
                      "
                      @click="openStatusModal(participant)"
                    >
                      {{ participant.status === "registered" ? "Nonaktifkan" : "Aktifkan" }}
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="filteredParticipants.length === 0">
                <td colspan="6" class="px-4 py-12 text-center">
                  <p class="text-sm font-medium text-gray-700">Tidak ada peserta ditemukan.</p>
                  <p class="mt-1 text-xs text-gray-500">
                    Periksa kata kunci pencarian atau tambahkan peserta baru.
                  </p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>

    <Teleport to="body">
      <div
        v-if="showActionModal && selectedParticipant && actionConfig"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 p-4"
        @click.self="closeActionModal"
      >
        <div
          class="w-full max-w-md overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl"
          role="dialog"
          aria-modal="true"
          aria-labelledby="participant-action-title"
        >
          <div class="border-b border-slate-100 px-5 py-4">
            <div class="flex items-start justify-between gap-3">
              <div>
                <h3 id="participant-action-title" class="text-base font-semibold text-slate-900">
                  {{ actionConfig.title }}
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                  {{ selectedParticipant.student.name }}
                  · {{ selectedParticipant.student.student_number }}
                </p>
              </div>

              <button
                type="button"
                :disabled="actionProcessing"
                class="rounded-lg p-1 text-slate-400 hover:bg-slate-100"
                @click="closeActionModal"
              >
                &times;
              </button>
            </div>
          </div>

          <div class="px-5 py-4">
            <p class="text-sm leading-relaxed text-slate-600">
              {{ actionConfig.description }}
            </p>

            <div
              v-if="selectedAction === 'reset'"
              class="mt-3 rounded-lg border border-red-100 bg-red-50 px-3 py-2 text-xs text-red-700"
            >
              <strong>Perhatian:</strong>
              tindakan ini menghapus attempt saat ini, termasuk jawaban dan snapshot 20 soal. Hasil
              attempt tersebut tidak dapat dipulihkan.
            </div>

            <div
              v-else
              class="mt-3 rounded-lg border border-amber-100 bg-amber-50 px-3 py-2 text-xs text-amber-700"
            >
              Resume token lama akan dicabut. Peserta harus membuka kuis kembali dari perangkat baru
              menggunakan NIM. Sesi dan sisa waktu tetap dipertahankan.
            </div>
          </div>

          <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-3">
            <button
              type="button"
              :disabled="actionProcessing"
              class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50"
              @click="closeActionModal"
            >
              Batal
            </button>

            <button
              type="button"
              :disabled="actionProcessing"
              class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-60"
              :class="actionConfig.buttonClass"
              @click="executeParticipantAction"
            >
              <svg
                v-if="actionProcessing"
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

              {{ actionProcessing ? "Memproses..." : actionConfig.button }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <Teleport to="body">
      <div
        v-if="showStatusModal && selectedParticipant"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 p-4"
        @click.self="closeStatusModal"
      >
        <div
          class="w-full max-w-md overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl"
          role="dialog"
          aria-modal="true"
          aria-labelledby="status-modal-title"
        >
          <div class="flex items-start gap-3 border-b border-slate-100 px-5 py-4">
            <div
              class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
              :class="
                nextParticipantStatus === 'disabled'
                  ? 'bg-red-50 text-red-600'
                  : 'bg-emerald-50 text-emerald-600'
              "
            >
              <svg
                v-if="nextParticipantStatus === 'disabled'"
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
              >
                <circle cx="12" cy="12" r="10" />
                <path d="M15 9l-6 6M9 9l6 6" />
              </svg>

              <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
              >
                <path d="M20 6L9 17l-5-5" />
                <path d="M22 12a10 10 0 1 1-5.9-9.1" />
              </svg>
            </div>

            <div class="min-w-0 flex-1">
              <h3 id="status-modal-title" class="text-base font-semibold text-slate-900">
                {{ statusActionLabel }} Akses Peserta
              </h3>

              <p class="mt-1 text-sm text-slate-500">
                Konfirmasi perubahan status pendaftaran peserta berikut.
              </p>
            </div>

            <button
              type="button"
              @click="closeStatusModal"
              :disabled="processingId !== null"
              class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 disabled:opacity-50"
              aria-label="Tutup modal"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
              >
                <path d="M18 6L6 18M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="px-5 py-4">
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
              <p class="text-sm font-semibold text-slate-900">
                {{ selectedParticipant.student.name }}
              </p>

              <p class="mt-1 text-xs text-slate-500">
                {{ selectedParticipant.student.student_number }}
                <span v-if="selectedParticipant.student.program_study">
                  · {{ selectedParticipant.student.program_study }}
                </span>
              </p>
            </div>

            <p class="mt-4 text-sm leading-relaxed text-slate-600">
              <template v-if="nextParticipantStatus === 'disabled'">
                Peserta ini tidak akan dapat memulai sesi kuis selama status pendaftarannya
                dinonaktifkan.
              </template>

              <template v-else>
                Peserta ini akan kembali memiliki akses untuk mengikuti kuis selama memenuhi
                ketentuan pelaksanaan kuis.
              </template>
            </p>

            <div
              class="mt-3 rounded-lg px-3 py-2 text-xs"
              :class="
                nextParticipantStatus === 'disabled'
                  ? 'bg-red-50 text-red-700'
                  : 'bg-emerald-50 text-emerald-700'
              "
            >
              Status baru:
              <span class="font-semibold">
                {{ nextParticipantStatus === "disabled" ? "Nonaktif" : "Aktif" }}
              </span>
            </div>
          </div>

          <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-3">
            <button
              type="button"
              @click="closeStatusModal"
              :disabled="processingId !== null"
              class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50"
            >
              Batal
            </button>

            <button
              type="button"
              @click="toggleStatus"
              :disabled="processingId !== null"
              class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-60"
              :class="
                nextParticipantStatus === 'disabled'
                  ? 'bg-red-600 hover:bg-red-700'
                  : 'bg-emerald-600 hover:bg-emerald-700'
              "
            >
              <svg
                v-if="processingId !== null"
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

              {{ processingId !== null ? "Memproses..." : statusActionLabel + " Peserta" }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Floating notifications -->
    <Teleport to="body">
      <div
        class="pointer-events-none fixed right-4 top-4 z-[110] flex w-[calc(100%-2rem)] max-w-sm flex-col gap-2"
        aria-live="polite"
      >
        <Transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="translate-y-[-8px] opacity-0"
          enter-to-class="translate-y-0 opacity-100"
          leave-active-class="transition duration-150 ease-in"
          leave-from-class="opacity-100"
          leave-to-class="translate-x-4 opacity-0"
        >
          <div
            v-if="errorMessage"
            class="pointer-events-auto flex items-start gap-3 rounded-lg border border-red-200 bg-white p-3 shadow-lg"
            role="alert"
          >
            <p class="flex-1 text-sm text-red-700">
              {{ errorMessage }}
            </p>
            <button
              type="button"
              class="text-gray-400 hover:text-gray-700"
              @click="clearNotification('error')"
            >
              &times;
            </button>
          </div>
        </Transition>

        <Transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="translate-y-[-8px] opacity-0"
          enter-to-class="translate-y-0 opacity-100"
          leave-active-class="transition duration-150 ease-in"
          leave-from-class="opacity-100"
          leave-to-class="translate-x-4 opacity-0"
        >
          <div
            v-if="successMessage"
            class="pointer-events-auto flex items-start gap-3 rounded-lg border border-green-200 bg-white p-3 shadow-lg"
            role="status"
          >
            <p class="flex-1 text-sm text-green-700">
              {{ successMessage }}
            </p>
            <button
              type="button"
              class="text-gray-400 hover:text-gray-700"
              @click="clearNotification('success')"
            >
              &times;
            </button>
          </div>
        </Transition>
      </div>
    </Teleport>
  </AdminLayout>
</template>
