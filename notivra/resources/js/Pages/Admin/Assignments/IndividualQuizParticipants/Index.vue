<script setup>
import { computed, ref } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
  assignment: { type: Object, required: true },
  participants: { type: Array, default: () => [] },
});

const studentNumber = ref("");
const search = ref("");
const processing = ref(null);
const errorMessage = ref("");
const successMessage = ref("");

const baseUrl = computed(
  () =>
    `/admin/assignments/${encodeURIComponent(props.assignment.slug)}/individual-quiz/participants`,
);

const filteredParticipants = computed(() => {
  const term = search.value.trim().toLowerCase();

  if (!term) return props.participants;

  return props.participants.filter((participant) => {
    const name = participant.student?.name ?? "";
    const number = participant.student?.student_number ?? "";
    const program = participant.student?.program_study ?? "";

    return [name, number, program].some((value) => String(value).toLowerCase().includes(term));
  });
});

const summary = computed(() => ({
  total: props.participants.length,
  registered: props.participants.filter((item) => item.status === "registered").length,
  disabled: props.participants.filter((item) => item.status === "disabled").length,
  inProgress: props.participants.filter((item) => item.attempt?.status === "in_progress").length,
  completed: props.participants.filter((item) => item.attempt?.status === "completed").length,
}));

function csrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.content ?? "";
}

async function sendRequest(url, method, body = null) {
  const response = await fetch(url, {
    method,
    credentials: "same-origin",
    headers: {
      Accept: "application/json",
      "X-Requested-With": "XMLHttpRequest",
      "X-CSRF-TOKEN": csrfToken(),
      ...(body ? { "Content-Type": "application/json" } : {}),
    },
    ...(body ? { body: JSON.stringify(body) } : {}),
  });

  const data = await response.json().catch(() => ({}));

  if (!response.ok) {
    const validationMessage = data.errors ? Object.values(data.errors).flat().join(" ") : null;

    throw new Error(validationMessage || data.message || "Permintaan gagal diproses.");
  }

  return data;
}

function reloadParticipants() {
  router.reload({
    only: ["participants"],
    preserveScroll: true,
    onFinish: () => {
      processing.value = null;
    },
  });
}

async function registerParticipant() {
  errorMessage.value = "";
  successMessage.value = "";

  if (!studentNumber.value.trim()) {
    errorMessage.value = "Masukkan NIM mahasiswa.";
    return;
  }

  processing.value = "register";

  try {
    const data = await sendRequest(baseUrl.value, "POST", {
      student_number: studentNumber.value.trim(),
    });

    studentNumber.value = "";
    successMessage.value = data.message ?? "Peserta berhasil didaftarkan.";
    reloadParticipants();
  } catch (error) {
    errorMessage.value = error.message;
    processing.value = null;
  }
}

async function toggleStatus(participant) {
  errorMessage.value = "";
  successMessage.value = "";

  const nextStatus = participant.status === "registered" ? "disabled" : "registered";
  const actionLabel = nextStatus === "disabled" ? "menonaktifkan" : "mengaktifkan";

  if (
    !window.confirm(
      `Yakin ingin ${actionLabel} peserta ${participant.student.name} (${participant.student.student_number})?`,
    )
  ) {
    return;
  }

  processing.value = `status-${participant.id}`;

  try {
    const data = await sendRequest(`${baseUrl.value}/${participant.id}/status`, "PATCH", {
      status: nextStatus,
    });

    successMessage.value = data.message ?? "Status peserta berhasil diperbarui.";
    reloadParticipants();
  } catch (error) {
    errorMessage.value = error.message;
    processing.value = null;
  }
}

function participantStatusLabel(status) {
  return status === "registered" ? "Aktif" : "Dinonaktifkan";
}

function attemptStatusLabel(status) {
  return (
    {
      pending: "Belum dimulai",
      in_progress: "Sedang dikerjakan",
      completed: "Selesai",
      expired: "Kedaluwarsa",
      invalid: "Tidak valid",
    }[status] ?? "Belum memulai"
  );
}

function statusClass(status) {
  return status === "registered" ? "bg-emerald-100 text-emerald-800" : "bg-gray-100 text-gray-700";
}

function attemptClass(status) {
  return (
    {
      pending: "bg-gray-100 text-gray-700",
      in_progress: "bg-blue-100 text-blue-800",
      completed: "bg-emerald-100 text-emerald-800",
      expired: "bg-red-100 text-red-800",
      invalid: "bg-red-100 text-red-800",
    }[status] ?? "bg-gray-100 text-gray-700"
  );
}

function formatDate(value) {
  if (!value) return "—";

  return new Intl.DateTimeFormat("id-ID", {
    dateStyle: "medium",
    timeStyle: "short",
  }).format(new Date(value));
}
</script>

<template>
  <AdminLayout :title="`Peserta Kuis Individual — ${assignment.title}`">
    <div class="mx-auto w-full max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
      <div class="mb-6">
        <p class="text-sm text-gray-500">Admin / Assignments / Kuis Individual / Peserta</p>

        <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">
              Peserta Kuis Individual
            </h1>
            <p class="mt-2 text-sm text-gray-600">{{ assignment.title }}</p>
            <p class="mt-1 text-sm text-gray-500">
              Durasi kuis: {{ assignment.duration_minutes }} menit
            </p>
          </div>

          <div class="flex flex-wrap gap-2">
            <Link
              href="/admin/assignments/individual-quiz-results"
              class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Laporan hasil individual
            </Link>
          </div>
        </div>
      </div>

      <div
        v-if="errorMessage"
        role="alert"
        class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
      >
        {{ errorMessage }}
      </div>

      <div
        v-if="successMessage"
        role="status"
        class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
      >
        {{ successMessage }}
      </div>

      <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-5">
        <section class="rounded-xl border border-gray-200 bg-white p-4">
          <p class="text-sm text-gray-500">Total peserta</p>
          <p class="mt-1 text-2xl font-semibold">{{ summary.total }}</p>
        </section>
        <section class="rounded-xl border border-gray-200 bg-white p-4">
          <p class="text-sm text-gray-500">Aktif</p>
          <p class="mt-1 text-2xl font-semibold">{{ summary.registered }}</p>
        </section>
        <section class="rounded-xl border border-gray-200 bg-white p-4">
          <p class="text-sm text-gray-500">Nonaktif</p>
          <p class="mt-1 text-2xl font-semibold">{{ summary.disabled }}</p>
        </section>
        <section class="rounded-xl border border-gray-200 bg-white p-4">
          <p class="text-sm text-gray-500">Sedang mengerjakan</p>
          <p class="mt-1 text-2xl font-semibold">{{ summary.inProgress }}</p>
        </section>
        <section class="rounded-xl border border-gray-200 bg-white p-4">
          <p class="text-sm text-gray-500">Selesai</p>
          <p class="mt-1 text-2xl font-semibold">{{ summary.completed }}</p>
        </section>
      </div>

      <section class="mb-6 rounded-xl border border-gray-200 bg-white p-5">
        <h2 class="text-lg font-semibold">Daftarkan peserta</h2>
        <p class="mt-1 text-sm text-gray-600">
          Masukkan NIM yang sudah tercatat pada data mahasiswa.
        </p>

        <form class="mt-4 flex flex-col gap-3 sm:flex-row" @submit.prevent="registerParticipant">
          <input
            v-model="studentNumber"
            type="text"
            maxlength="100"
            required
            placeholder="NIM mahasiswa"
            class="min-w-0 flex-1 rounded-lg border border-gray-300 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
          />
          <button
            type="submit"
            :disabled="processing !== null"
            class="rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-800 disabled:opacity-50"
          >
            {{ processing === "register" ? "Mendaftarkan…" : "Daftarkan peserta" }}
          </button>
        </form>
      </section>

      <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div
          class="flex flex-col gap-3 border-b border-gray-200 p-5 sm:flex-row sm:items-center sm:justify-between"
        >
          <div>
            <h2 class="text-lg font-semibold">Daftar peserta</h2>
            <p class="mt-1 text-sm text-gray-500">
              Menampilkan {{ filteredParticipants.length }} dari {{ participants.length }} peserta
            </p>
          </div>
          <input
            v-model="search"
            type="search"
            placeholder="Cari nama, NIM, atau program studi"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm sm:max-w-sm"
          />
        </div>

        <div v-if="filteredParticipants.length === 0" class="p-8 text-center text-sm text-gray-500">
          Tidak ada peserta yang cocok dengan pencarian.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full min-w-[950px] divide-y divide-gray-200 text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
              <tr>
                <th class="px-5 py-3">Mahasiswa</th>
                <th class="px-5 py-3">Program studi</th>
                <th class="px-5 py-3">Status peserta</th>
                <th class="px-5 py-3">Status kuis</th>
                <th class="px-5 py-3">Mulai</th>
                <th class="px-5 py-3">Batas sesi</th>
                <th class="px-5 py-3">Selesai</th>
                <th class="px-5 py-3">Tindakan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="participant in filteredParticipants" :key="participant.id">
                <td class="px-5 py-4">
                  <p class="font-medium text-gray-900">{{ participant.student.name }}</p>
                  <p class="mt-1 font-mono text-xs text-gray-500">
                    {{ participant.student.student_number }}
                  </p>
                </td>
                <td class="px-5 py-4 text-gray-600">
                  {{ participant.student.program_study || "—" }}
                </td>
                <td class="px-5 py-4">
                  <span
                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                    :class="statusClass(participant.status)"
                  >
                    {{ participantStatusLabel(participant.status) }}
                  </span>
                </td>
                <td class="px-5 py-4">
                  <span
                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                    :class="attemptClass(participant.attempt?.status)"
                  >
                    {{ attemptStatusLabel(participant.attempt?.status) }}
                  </span>
                </td>
                <td class="px-5 py-4 text-gray-600">
                  {{ formatDate(participant.attempt?.started_at) }}
                </td>
                <td class="px-5 py-4 text-gray-600">
                  {{ formatDate(participant.attempt?.expires_at) }}
                </td>
                <td class="px-5 py-4 text-gray-600">
                  {{
                    formatDate(participant.attempt?.completed_at ?? participant.attempt?.expired_at)
                  }}
                </td>
                <td class="px-5 py-4">
                  <button
                    type="button"
                    :disabled="processing !== null || participant.attempt?.status === 'in_progress'"
                    class="rounded-lg border px-3 py-2 text-xs font-medium disabled:cursor-not-allowed disabled:opacity-40"
                    :class="
                      participant.status === 'registered'
                        ? 'border-red-200 text-red-700 hover:bg-red-50'
                        : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50'
                    "
                    @click="toggleStatus(participant)"
                  >
                    {{
                      processing === `status-${participant.id}`
                        ? "Memproses…"
                        : participant.status === "registered"
                          ? "Nonaktifkan"
                          : "Aktifkan"
                    }}
                  </button>
                  <p
                    v-if="participant.attempt?.status === 'in_progress'"
                    class="mt-1 max-w-36 text-xs text-gray-500"
                  >
                    Sesi aktif harus berakhir sebelum peserta dinonaktifkan.
                  </p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </AdminLayout>
</template>
