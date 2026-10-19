<script setup>
import { computed, ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

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

const page = usePage();
const processingAction = ref(null);

const baseUrl = computed(() => {
  return `/admin/assignments/${encodeURIComponent(props.assignment.slug)}/groups/${encodeURIComponent(props.group.reference_code)}`;
});

const flashSuccess = computed(() => page.props.flash?.success ?? null);
const flashError = computed(() => page.props.flash?.error ?? null);

const statusLabels = {
  draft: "Draft",
  verification_in_progress: "Verifikasi berlangsung",
  ready_for_upload: "Siap upload",
  pending: "Menunggu dimulai",
  in_progress: "Sedang dikerjakan",
  completed: "Selesai",
  expired: "Kedaluwarsa",
  submitted: "Sudah dikumpulkan",
  under_review: "Dalam pemeriksaan",
  reviewed: "Selesai diperiksa",
};

const statusClasses = {
  draft: "bg-gray-100 text-gray-700",
  verification_in_progress: "bg-amber-100 text-amber-800",
  ready_for_upload: "bg-emerald-100 text-emerald-800",
  pending: "bg-gray-100 text-gray-700",
  in_progress: "bg-blue-100 text-blue-800",
  completed: "bg-emerald-100 text-emerald-800",
  expired: "bg-red-100 text-red-800",
  submitted: "bg-blue-100 text-blue-800",
  under_review: "bg-violet-100 text-violet-800",
  reviewed: "bg-gray-100 text-gray-700",
};

function statusLabel(status) {
  return statusLabels[status] ?? status ?? "Tidak diketahui";
}

function statusClass(status) {
  return statusClasses[status] ?? "bg-gray-100 text-gray-700";
}

function formatDate(value) {
  if (!value) return "—";

  return new Intl.DateTimeFormat("id-ID", {
    dateStyle: "medium",
    timeStyle: "short",
  }).format(new Date(value));
}

function bypassUpload() {
  if (props.group.submission) {
    window.alert("Grup sudah memiliki submission. Bypass tidak dapat dilakukan.");
    return;
  }

  const confirmed = window.confirm(
    `Bypass upload wizard untuk grup "${props.group.group_name}"?\n\n` +
      "Anggota tidak harus menyelesaikan verifikasi kuis. " +
      "Status publikasi dan tenggat assignment tetap berlaku.",
  );

  if (!confirmed) return;

  processingAction.value = "bypass";

  router.post(
    `${baseUrl.value}/bypass-upload`,
    {},
    {
      preserveScroll: true,
      onFinish: () => {
        processingAction.value = null;
      },
    },
  );
}

function resetSession(member) {
  if (!member.has_verification) {
    window.alert("Anggota ini belum memiliki sesi verifikasi.");
    return;
  }

  if (member.verification_status === "completed") {
    window.alert("Sesi yang sudah selesai tidak dapat di-reset melalui tindakan ini.");
    return;
  }

  const confirmed = window.confirm(
    `Reset sesi kuis untuk ${member.name} (${member.student_number})?\n\n` +
      "Jawaban dan token sesi lama akan dihapus. Anggota harus memulai sesi baru.",
  );

  if (!confirmed) return;

  processingAction.value = `reset-${member.id}`;

  router.post(
    `${baseUrl.value}/members/${member.id}/reset-session`,
    {},
    {
      preserveScroll: true,
      onFinish: () => {
        processingAction.value = null;
      },
    },
  );
}

function removeDevice(member) {
  if (!member.has_registered_device) {
    window.alert("Tidak ditemukan registrasi perangkat untuk anggota ini.");
    return;
  }

  const warning =
    member.verification_status === "in_progress"
      ? "\n\nPerhatian: sesi sedang berlangsung. Menghapus perangkat saja dapat membuat sesi tidak dapat dipulihkan. Jika perangkat hilang, pertimbangkan reset sesi juga."
      : "";

  const confirmed = window.confirm(
    `Hapus registrasi perangkat kuis untuk ${member.name} (${member.student_number})?` + warning,
  );

  if (!confirmed) return;

  processingAction.value = `device-${member.id}`;

  router.delete(`${baseUrl.value}/members/${member.id}/device`, {
    preserveScroll: true,
    onFinish: () => {
      processingAction.value = null;
    },
  });
}
</script>

<template>
  <AdminLayout :title="`Detail Grup — ${group.group_name}`">
    <div class="mx-auto w-full max-w-7xl px-4 py-2 sm:px-6 lg:px-8">
      <div class="mb-6">
        <p class="text-sm text-gray-500">
          Admin / Assignments /
          <Link
            :href="`/admin/assignments/${encodeURIComponent(assignment.slug)}/groups`"
            class="hover:text-gray-900 hover:underline"
          >
            Grup
          </Link>
          / Detail
        </p>

        <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">
              {{ group.group_name }}
            </h1>
            <p class="mt-1 font-mono text-sm text-gray-500">
              {{ group.reference_code }}
            </p>
            <p class="mt-2 text-sm text-gray-600">Assignment: {{ assignment.title }}</p>
          </div>

          <Link
            :href="`/admin/assignments/${encodeURIComponent(assignment.slug)}/groups`"
            class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Kembali ke daftar grup
          </Link>
        </div>
      </div>

      <div
        v-if="flashSuccess"
        role="status"
        class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
      >
        {{ flashSuccess }}
      </div>

      <div
        v-if="flashError"
        role="alert"
        class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
      >
        {{ flashError }}
      </div>

      <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
        <section class="rounded-xl border border-gray-200 bg-white p-5">
          <p class="text-sm text-gray-500">Status grup</p>
          <span
            class="mt-3 inline-flex rounded-full px-3 py-1 text-sm font-medium"
            :class="statusClass(group.status)"
          >
            {{ statusLabel(group.status) }}
          </span>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-5">
          <p class="text-sm text-gray-500">Jumlah anggota</p>
          <p class="mt-2 text-2xl font-semibold text-gray-900">
            {{ group.members?.length ?? 0 }}
          </p>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-5">
          <p class="text-sm text-gray-500">Status bypass</p>
          <p
            class="mt-2 text-base font-semibold"
            :class="group.upload_bypassed_at ? 'text-amber-700' : 'text-gray-900'"
          >
            {{ group.upload_bypassed_at ? "Aktif" : "Tidak aktif" }}
          </p>
          <p v-if="group.upload_bypassed_at" class="mt-1 text-xs text-gray-500">
            Dicatat {{ formatDate(group.upload_bypassed_at) }}
          </p>
        </section>
      </div>

      <section class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div
          class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
        >
          <div>
            <h2 class="font-semibold text-gray-900">Kontrol upload</h2>
            <p class="mt-1 text-sm text-gray-500">
              Bypass hanya mengabaikan syarat verifikasi anggota, bukan tenggat assignment.
            </p>
          </div>

          <button
            type="button"
            :disabled="
              Boolean(processingAction) ||
              Boolean(group.submission) ||
              Boolean(group.upload_bypassed_at)
            "
            class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50"
            :class="group.upload_bypassed_at ? 'bg-amber-600' : 'bg-gray-900 hover:bg-gray-700'"
            @click="bypassUpload"
          >
            <span v-if="processingAction === 'bypass'">Memproses...</span>
            <span v-else-if="group.upload_bypassed_at">Bypass sudah aktif</span>
            <span v-else-if="group.submission">Submission sudah ada</span>
            <span v-else>Bypass upload wizard</span>
          </button>
        </div>

        <div class="px-5 py-4">
          <template v-if="group.submission">
            <p class="text-sm font-medium text-gray-900">Submission sudah tersedia</p>
            <p class="mt-1 text-sm text-gray-500">
              {{ group.submission.original_filename }}
            </p>
            <p class="mt-1 text-xs text-gray-500">
              Dikirim: {{ formatDate(group.submission.submitted_at) }}
              · Pemeriksaan duplikasi:
              {{ group.submission.duplicate_status ?? "Belum diperiksa" }}
            </p>
          </template>

          <p v-else class="text-sm text-gray-600">Belum ada submission untuk grup ini.</p>
        </div>
      </section>

      <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4">
          <h2 class="font-semibold text-gray-900">Anggota dan sesi kuis</h2>
          <p class="mt-1 text-sm text-gray-500">
            Reset sesi dan penghapusan perangkat merupakan dua operasi terpisah.
          </p>
        </div>

        <div v-if="!group.members?.length" class="px-5 py-12 text-center">
          <p class="font-medium text-gray-900">Belum ada anggota</p>
          <p class="mt-1 text-sm text-gray-500">Data anggota grup akan tampil di sini.</p>
        </div>

        <div v-else class="divide-y divide-gray-200">
          <article v-for="member in group.members" :key="member.id" class="p-5">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
              <div class="min-w-0">
                <h3 class="font-medium text-gray-900">
                  {{ member.name }}
                </h3>
                <p class="mt-1 text-sm text-gray-600">
                  {{ member.student_number }}
                </p>
                <p class="mt-1 text-sm text-gray-500">
                  {{ member.program_study || "Program studi tidak tersedia" }}
                </p>

                <div class="mt-3 flex flex-wrap gap-2">
                  <span
                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                    :class="statusClass(member.verification_status)"
                  >
                    Kuis: {{ statusLabel(member.verification_status) }}
                  </span>

                  <span
                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                    :class="
                      member.has_registered_device
                        ? 'bg-blue-100 text-blue-800'
                        : 'bg-gray-100 text-gray-600'
                    "
                  >
                    {{
                      member.has_registered_device
                        ? "Perangkat terdaftar"
                        : "Tidak ada perangkat terdaftar"
                    }}
                  </span>
                </div>
              </div>

              <div class="flex flex-col gap-2 sm:flex-row xl:shrink-0">
                <button
                  type="button"
                  :disabled="
                    Boolean(processingAction) ||
                    !member.has_verification ||
                    member.verification_status === 'completed'
                  "
                  class="inline-flex items-center justify-center rounded-lg border border-amber-300 px-3 py-2 text-sm font-medium text-amber-800 hover:bg-amber-50 disabled:cursor-not-allowed disabled:opacity-40"
                  @click="resetSession(member)"
                >
                  <span v-if="processingAction === `reset-${member.id}`"> Mereset... </span>
                  <span v-else>Reset sesi kuis</span>
                </button>

                <button
                  type="button"
                  :disabled="Boolean(processingAction) || !member.has_registered_device"
                  class="inline-flex items-center justify-center rounded-lg border border-red-300 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40"
                  @click="removeDevice(member)"
                >
                  <span v-if="processingAction === `device-${member.id}`"> Menghapus... </span>
                  <span v-else>Hapus perangkat</span>
                </button>
              </div>
            </div>
          </article>
        </div>
      </section>
    </div>
  </AdminLayout>
</template>
