<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
  assignment: {
    type: Object,
    required: true,
  },
});

const baseUrl = `/assignments/${encodeURIComponent(props.assignment.slug)}/individual-quiz`;

const deviceStorageKey = `notivra.individual-quiz.${props.assignment.slug}.device`;
const resumeStorageKey = `notivra.individual-quiz.${props.assignment.slug}.resume`;

const studentNumber = ref("");
const deviceToken = ref("");
const resumeToken = ref("");

const session = ref(null);
const result = ref(null);

const loading = ref(false);
const submitting = ref(false);
const savingPosition = ref(null);
const showFinishModal = ref(false);

const errorMessage = ref("");
const successMessage = ref("");

const remainingSeconds = ref(0);
const currentQuestionIndex = ref(0);

let timerInterval = null;

const questions = computed(() => session.value?.questions ?? []);

const answers = computed(() => session.value?.answers ?? {});

const totalQuestions = computed(() => questions.value.length);

const currentQuestion = computed(() => questions.value[currentQuestionIndex.value] ?? null);

const currentQuestionNumber = computed(() => currentQuestionIndex.value + 1);

const isFirstQuestion = computed(() => currentQuestionIndex.value === 0);

const isLastQuestion = computed(() => currentQuestionIndex.value >= totalQuestions.value - 1);

const isActive = computed(() => session.value?.status === "in_progress");

const isFinished = computed(
  () =>
    session.value?.status === "completed" ||
    session.value?.status === "expired" ||
    result.value !== null,
);

const answeredCount = computed(() => {
  return questions.value.filter((question) => {
    const answer = answers.value?.[question.position];

    return ["A", "B", "C", "D"].includes(String(answer ?? "").toUpperCase());
  }).length;
});

const unansweredCount = computed(() => Math.max(totalQuestions.value - answeredCount.value, 0));

const progressPercentage = computed(() => {
  if (!totalQuestions.value) return 0;

  return Math.round((answeredCount.value / totalQuestions.value) * 100);
});

const currentAnswer = computed(() => {
  if (!currentQuestion.value) return "";

  return String(answers.value?.[currentQuestion.value.position] ?? "").toUpperCase();
});

const formattedTime = computed(() => {
  const seconds = Math.max(remainingSeconds.value, 0);

  const hours = Math.floor(seconds / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  const remainder = seconds % 60;

  if (hours > 0) {
    return [
      String(hours).padStart(2, "0"),
      String(minutes).padStart(2, "0"),
      String(remainder).padStart(2, "0"),
    ].join(":");
  }

  return [String(minutes).padStart(2, "0"), String(remainder).padStart(2, "0")].join(":");
});

const timerIsCritical = computed(() => remainingSeconds.value <= 300);

const statusLabel = computed(() => {
  switch (session.value?.status) {
    case "in_progress":
      return "Sedang berlangsung";
    case "completed":
      return "Selesai";
    case "expired":
      return "Waktu habis";
    default:
      return "Belum dimulai";
  }
});

function getCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
}

function createDeviceToken() {
  const bytes = new Uint8Array(32);
  window.crypto.getRandomValues(bytes);

  return Array.from(bytes)
    .map((byte) => byte.toString(16).padStart(2, "0"))
    .join("");
}

function initializeDeviceToken() {
  let token = localStorage.getItem(deviceStorageKey);

  if (!token || !/^[a-f0-9]{64}$/.test(token)) {
    token = createDeviceToken();
    localStorage.setItem(deviceStorageKey, token);
  }

  deviceToken.value = token;
}

async function request(path, options = {}) {
  const headers = {
    Accept: "application/json",
    "X-Requested-With": "XMLHttpRequest",
    "X-Device-Token": deviceToken.value,
    ...options.headers,
  };

  const csrfToken = getCsrfToken();

  if (csrfToken) {
    headers["X-CSRF-TOKEN"] = csrfToken;
  }

  if (resumeToken.value) {
    headers.Authorization = `Bearer ${resumeToken.value}`;
  }

  const response = await fetch(`${baseUrl}${path}`, {
    ...options,
    headers,
  });

  let data = {};

  try {
    data = await response.json();
  } catch {
    data = {};
  }

  if (!response.ok) {
    const error = new Error(data.message || "Terjadi kesalahan saat memproses permintaan.");

    error.status = response.status;
    error.data = data;

    throw error;
  }

  return data;
}

function stopTimer() {
  if (timerInterval) {
    clearInterval(timerInterval);
    timerInterval = null;
  }
}

function startTimer() {
  stopTimer();

  if (!isActive.value) return;

  timerInterval = setInterval(() => {
    if (remainingSeconds.value > 0) {
      remainingSeconds.value -= 1;
      return;
    }

    stopTimer();
    finishQuiz(true);
  }, 1000);
}

function applySession(newSession) {
  session.value = newSession;

  remainingSeconds.value = Math.max(Number(newSession.remaining_seconds ?? 0), 0);

  if (currentQuestionIndex.value >= questions.value.length) {
    currentQuestionIndex.value = Math.max(questions.value.length - 1, 0);
  }

  if (newSession.status === "in_progress") {
    startTimer();
  } else {
    stopTimer();
  }
}

function clearResumeToken() {
  resumeToken.value = "";
  localStorage.removeItem(resumeStorageKey);
}

async function restoreSession() {
  if (!resumeToken.value) return;

  loading.value = true;
  errorMessage.value = "";

  try {
    const response = await request("/session");

    applySession(response.session);
    successMessage.value = "Sesi kuis berhasil dipulihkan.";
  } catch (error) {
    if ([401, 403, 404].includes(error.status)) {
      clearResumeToken();
    }

    errorMessage.value = error.message || "Sesi kuis tidak dapat dipulihkan.";
  } finally {
    loading.value = false;
  }
}

async function startQuiz() {
  if (!studentNumber.value.trim()) {
    errorMessage.value = "Masukkan NIM terlebih dahulu.";
    return;
  }

  loading.value = true;
  errorMessage.value = "";
  successMessage.value = "";

  try {
    const response = await request("/start", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        student_number: studentNumber.value.trim(),
        device_token: deviceToken.value,
      }),
    });

    resumeToken.value = response.resume_token;

    localStorage.setItem(resumeStorageKey, response.resume_token);

    currentQuestionIndex.value = 0;

    applySession(response.session);

    successMessage.value = response.message;
  } catch (error) {
    errorMessage.value = error.message || "Kuis tidak dapat dimulai.";
  } finally {
    loading.value = false;
  }
}

async function saveAnswer(option) {
  if (
    !isActive.value ||
    !currentQuestion.value ||
    savingPosition.value !== null ||
    submitting.value
  ) {
    return;
  }

  const position = currentQuestion.value.position;

  savingPosition.value = position;
  errorMessage.value = "";
  successMessage.value = "";

  try {
    const response = await request(`/answers/${position}`, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        answer: option,
      }),
    });

    applySession(response.session);
    successMessage.value = "Jawaban tersimpan.";
  } catch (error) {
    errorMessage.value = error.message || "Jawaban gagal disimpan.";
  } finally {
    savingPosition.value = null;
  }
}

function previousQuestion() {
  if (
    !currentQuestion.value ||
    savingPosition.value !== null ||
    submitting.value ||
    currentQuestionIndex.value <= 0
  ) {
    return;
  }

  currentQuestionIndex.value -= 1;
  errorMessage.value = "";
  successMessage.value = "";
}

function nextQuestion() {
  if (
    !currentQuestion.value ||
    savingPosition.value !== null ||
    submitting.value ||
    currentQuestionIndex.value >= totalQuestions.value - 1
  ) {
    return;
  }

  currentQuestionIndex.value += 1;
  errorMessage.value = "";
  successMessage.value = "";
}

function finishQuiz(autoExpired = false) {
  if (!resumeToken.value || submitting.value) return;

  if (autoExpired) {
    submitQuiz();
    return;
  }

  showFinishModal.value = true;
}

async function submitQuiz() {
  if (!resumeToken.value || submitting.value) return;

  submitting.value = true;
  showFinishModal.value = false;
  errorMessage.value = "";
  successMessage.value = "";

  try {
    const response = await request("/complete", {
      method: "POST",
    });

    result.value = response.result;

    if (session.value) {
      session.value.status = response.result.status;
    }

    remainingSeconds.value = 0;
    stopTimer();
    clearResumeToken();

    successMessage.value = response.message;
  } catch (error) {
    errorMessage.value = error.message || "Kuis gagal diselesaikan.";
  } finally {
    submitting.value = false;
  }
}

function formatDate(value) {
  if (!value) return "-";

  return new Intl.DateTimeFormat("id-ID", {
    dateStyle: "medium",
    timeStyle: "short",
  }).format(new Date(value));
}

function optionText(question, option) {
  return question[`option_${option.toLowerCase()}`] ?? "";
}

function scoreValue() {
  return result.value?.score ?? result.value?.correct_answers ?? 0;
}

function scoreTotal() {
  return result.value?.total_questions ?? totalQuestions.value;
}

onMounted(async () => {
  initializeDeviceToken();

  resumeToken.value = localStorage.getItem(resumeStorageKey) ?? "";

  if (resumeToken.value) {
    await restoreSession();
  }
});

onBeforeUnmount(() => {
  stopTimer();
});
</script>

<template>
  <AppLayout :title="`${assignment.title}`" margin="wide">
    <div class="mx-auto max-w-4xl space-y-3 px-3 py-3 sm:px-5 sm:py-4">
      <!-- Assignment header -->
      <header class="space-y-1">
        <div class="flex flex-wrap items-center gap-2">
          <span
            class="rounded-md bg-indigo-50 px-2 py-1 text-[11px] font-semibold uppercase tracking-wide text-indigo-700"
          >
            Ujian Tengah Semester (UTS)
          </span>

          <span
            v-if="session"
            class="rounded-md bg-gray-100 px-2 py-1 text-[11px] font-medium text-gray-600"
          >
            {{ statusLabel }}
          </span>
        </div>

        <h1 class="text-xl font-bold leading-tight text-gray-900 sm:text-2xl">
          {{ assignment.title }}
        </h1>

        <p class="text-xs text-gray-500 sm:text-sm">
          {{ assignment.description }}
        </p>

        <div v-if="!session" class="flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-gray-500">
          <span> Dibuka: {{ formatDate(assignment.opens_at) }} </span>
          <span> Ditutup: {{ formatDate(assignment.due_at) }} </span>
        </div>

        <div v-if="assignment.instructions && !session" class="mt-2 rounded-xl bg-slate-50">
          <h2 class="font-semibold text-slate-900">Petunjuk</h2>
          <p class="mt-2 whitespace-pre-line text-sm leading-3.5 text-slate-600">
            {{ assignment.instructions }}
          </p>
        </div>
      </header>

      <!-- Floating notifications -->
      <Teleport to="body">
        <div
          class="pointer-events-none fixed right-4 top-4 z-110 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-2"
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
              <div class="mt-0.5 shrink-0 text-red-600">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  class="h-5 w-5"
                >
                  <circle cx="12" cy="12" r="10" />
                  <path d="M12 8v4m0 4h.01" />
                </svg>
              </div>

              <p class="flex-1 text-sm text-gray-700">
                {{ errorMessage }}
              </p>

              <button
                type="button"
                class="shrink-0 text-gray-400 hover:text-gray-700"
                aria-label="Tutup notifikasi"
                @click="errorMessage = ''"
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
              <div class="mt-0.5 shrink-0 text-green-600">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  class="h-5 w-5"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                </svg>
              </div>

              <p class="flex-1 text-sm text-gray-700">
                {{ successMessage }}
              </p>

              <button
                type="button"
                class="shrink-0 text-gray-400 hover:text-gray-700"
                aria-label="Tutup notifikasi"
                @click="successMessage = ''"
              >
                &times;
              </button>
            </div>
          </Transition>
        </div>
      </Teleport>
      <!-- Loading -->
      <div
        v-if="loading"
        class="rounded-lg border border-gray-200 bg-white px-4 py-6 text-center text-sm text-gray-500"
      >
        Memuat sesi kuis...
      </div>

      <!-- Start screen -->
      <section
        v-else-if="!session && !result"
        class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5"
      >
        <div class="mb-4">
          <h2 class="text-base font-semibold text-gray-900">Identitas Peserta</h2>
          <p class="mt-1 text-xs text-gray-500">
            Masukkan NIM untuk memulai Ujian Tengah Semester.
          </p>
        </div>

        <form class="space-y-3" @submit.prevent="startQuiz">
          <div>
            <label for="student-number" class="mb-1 block text-xs font-medium text-gray-700">
              Nomor Induk Mahasiswa (NIM)
            </label>

            <input
              id="student-number"
              v-model="studentNumber"
              type="text"
              autocomplete="off"
              required
              placeholder="Masukkan NIM"
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
            />
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
          >
            Mulai UTS
          </button>
        </form>
      </section>

      <!-- Quiz session -->
      <template v-else-if="session">
        <!-- Session overview -->
        <section class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
              <p class="text-[11px] text-gray-500">Peserta</p>
              <p class="truncate text-sm font-semibold text-gray-900">
                {{ session.student?.name ?? studentNumber }}
              </p>
              <p class="text-[11px] text-gray-500">
                {{ session.student?.student_number ?? studentNumber }}
              </p>
            </div>

            <div class="flex items-center gap-2">
              <div class="rounded-lg bg-gray-50 px-3 py-2 text-center">
                <p class="text-[10px] text-gray-500">Sisa waktu</p>
                <p
                  class="font-mono text-lg font-bold tabular-nums"
                  :class="timerIsCritical && isActive ? 'text-red-600' : 'text-gray-900'"
                >
                  {{ formattedTime }}
                </p>
              </div>
            </div>
          </div>
        </section>

        <!-- Answer progress -->
        <section
          v-if="totalQuestions > 0"
          class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm"
        >
          <div class="mb-2 flex items-center justify-between gap-2">
            <h2 class="text-xs font-semibold text-gray-800">Progres pengerjaan</h2>

            <span class="text-xs font-semibold tabular-nums text-indigo-600">
              {{ progressPercentage }}%
            </span>
          </div>

          <div class="mb-2.5 h-2 overflow-hidden rounded-full bg-gray-100">
            <div
              class="h-full rounded-full bg-indigo-600 transition-all duration-300"
              :style="{ width: `${progressPercentage}%` }"
            />
          </div>

          <div class="grid grid-cols-3 gap-2 text-center">
            <div class="rounded-lg bg-green-50 px-2 py-1.5">
              <p class="text-base font-bold tabular-nums text-green-700">
                {{ answeredCount }}
              </p>
              <p class="text-[10px] text-green-700">Dijawab</p>
            </div>

            <div class="rounded-lg bg-amber-50 px-2 py-1.5">
              <p class="text-base font-bold tabular-nums text-amber-700">
                {{ unansweredCount }}
              </p>
              <p class="text-[10px] text-amber-700">Belum dijawab</p>
            </div>

            <div class="rounded-lg bg-gray-50 px-2 py-1.5">
              <p class="text-base font-bold tabular-nums text-gray-800">
                {{ totalQuestions }}
              </p>
              <p class="text-[10px] text-gray-500">Total soal</p>
            </div>
          </div>
        </section>

        <!-- Active quiz -->
        <section
          v-if="isActive && currentQuestion"
          class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm sm:p-4"
        >
          <!-- Question heading -->
          <div class="mb-3 flex items-center justify-between gap-3">
            <div>
              <p class="text-[11px] text-gray-500">
                Soal {{ currentQuestionNumber }} dari {{ totalQuestions }}
              </p>
              <h2 class="text-sm font-semibold text-gray-900">Pertanyaan</h2>
            </div>

            <span
              class="shrink-0 rounded-md px-2 py-1 text-[11px] font-medium"
              :class="currentAnswer ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700'"
            >
              {{ currentAnswer ? "Sudah dijawab" : "Belum dijawab" }}
            </span>
          </div>

          <!-- Question content -->
          <div class="mb-4 whitespace-pre-line text-sm leading-relaxed text-gray-800">
            {{ currentQuestion.question }}
          </div>

          <!-- Answer options -->
          <div class="space-y-2">
            <label
              v-for="option in ['A', 'B', 'C', 'D']"
              :key="option"
              class="flex cursor-pointer items-start gap-3 rounded-lg border px-3 py-2.5 transition"
              :class="[
                currentAnswer === option
                  ? 'border-indigo-500 bg-indigo-50'
                  : 'border-gray-200 hover:border-gray-300',
                savingPosition !== null || submitting ? 'cursor-not-allowed opacity-70' : '',
              ]"
            >
              <input
                type="radio"
                :name="`question-${currentQuestion.position}`"
                :value="option"
                :checked="currentAnswer === option"
                :disabled="savingPosition !== null || submitting"
                class="mt-0.5 h-4 w-4 shrink-0 accent-indigo-600"
                @change="saveAnswer(option)"
              />

              <span
                class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md text-[11px] font-bold"
                :class="
                  currentAnswer === option
                    ? 'bg-indigo-600 text-white'
                    : 'bg-gray-100 text-gray-600'
                "
              >
                {{ option }}
              </span>

              <span class="min-w-0 flex-1 text-sm leading-relaxed text-gray-700">
                {{ optionText(currentQuestion, option) }}
              </span>
            </label>
          </div>

          <!-- Saving indicator -->
          <p v-if="savingPosition !== null" class="mt-2 text-[11px] text-indigo-600">
            Menyimpan jawaban...
          </p>

          <!-- Navigation -->
          <div class="mt-4 flex items-center justify-between gap-2 border-t border-gray-100 pt-3">
            <button
              type="button"
              :disabled="isFirstQuestion || savingPosition !== null || submitting"
              class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"
              @click="previousQuestion"
            >
              ← Kembali
            </button>

            <button
              v-if="!isLastQuestion"
              type="button"
              :disabled="savingPosition !== null || submitting"
              class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
              @click="nextQuestion"
            >
              Lanjut →
            </button>

            <button
              v-else
              type="button"
              :disabled="savingPosition !== null || submitting"
              class="rounded-lg bg-green-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
              @click="finishQuiz(false)"
            >
              {{ submitting ? "Memproses..." : "Selesaikan Kuis" }}
            </button>
          </div>
        </section>

        <!-- Completed / expired -->
        <section
          v-else-if="isFinished"
          class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm"
        >
          <div class="mb-4 text-center">
            <div
              class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full"
              :class="
                session.status === 'completed'
                  ? 'bg-green-100 text-green-700'
                  : 'bg-amber-100 text-amber-700'
              "
            >
              <svg
                v-if="session.status === 'completed'"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                class="h-6 w-6"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
              </svg>

              <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                class="h-6 w-6"
              >
                <circle cx="12" cy="12" r="9" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" />
              </svg>
            </div>

            <h2 class="text-lg font-bold text-gray-900">
              {{ session.status === "completed" ? "Kuis selesai" : "Waktu kuis habis" }}
            </h2>

            <p class="mt-1 text-xs text-gray-500">
              {{ successMessage || "Sesi pengerjaan kuis telah berakhir." }}
            </p>
          </div>

          <div class="grid grid-cols-2 gap-2">
            <div class="rounded-lg bg-gray-50 p-3 text-center">
              <p class="text-xl font-bold tabular-nums text-gray-900">
                {{ answeredCount }}
              </p>
              <p class="text-[11px] text-gray-500">Soal dijawab</p>
            </div>

            <div class="rounded-lg bg-gray-50 p-3 text-center">
              <p class="text-xl font-bold tabular-nums text-gray-900">
                {{ unansweredCount }}
              </p>
              <p class="text-[11px] text-gray-500">Tidak dijawab</p>
            </div>

            <div class="col-span-2 rounded-lg bg-indigo-50 p-3 text-center">
              <p class="text-xl font-bold tabular-nums text-indigo-700">
                {{ progressPercentage }}%
              </p>
              <p class="text-[11px] text-indigo-700">Progres jawaban</p>
            </div>
          </div>

          <div v-if="result" class="mt-3 rounded-lg border border-gray-200 p-3 text-center">
            <p class="text-xs text-gray-500">Hasil penilaian</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">
              {{ scoreValue() }} / {{ scoreTotal() }}
            </p>
          </div>
        </section>

        <!-- Invalid session -->
        <section
          v-else
          class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800"
        >
          Tidak ada soal yang tersedia untuk sesi ini. Silakan hubungi administrator.
        </section>
      </template>
    </div>

    <!-- Finish confirmation modal -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="showFinishModal"
          class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-950/50 p-4"
          @click.self="showFinishModal = false"
        >
          <div
            class="w-full max-w-md rounded-xl bg-white p-5 shadow-2xl"
            role="dialog"
            aria-modal="true"
            aria-labelledby="finish-modal-title"
          >
            <div class="mb-4 flex items-start gap-3">
              <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  class="h-5 w-5"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 9v4m0 4h.01M10.3 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.7 3.86a2 2 0 0 0-3.4 0Z"
                  />
                </svg>
              </div>

              <div>
                <h2 id="finish-modal-title" class="text-base font-bold text-gray-900">
                  Selesaikan kuis?
                </h2>

                <p class="mt-1 text-sm leading-relaxed text-gray-500">
                  Pastikan seluruh jawaban sudah diperiksa sebelum mengakhiri pengerjaan.
                </p>
              </div>
            </div>

            <div class="mb-4 grid grid-cols-2 gap-2">
              <div class="rounded-lg bg-green-50 p-3 text-center">
                <p class="text-2xl font-bold tabular-nums text-green-700">
                  {{ answeredCount }}
                </p>
                <p class="text-xs text-green-700">Sudah dijawab</p>
              </div>

              <div class="rounded-lg bg-amber-50 p-3 text-center">
                <p class="text-2xl font-bold tabular-nums text-amber-700">
                  {{ unansweredCount }}
                </p>
                <p class="text-xs text-amber-700">Belum dijawab</p>
              </div>
            </div>

            <div
              v-if="unansweredCount > 0"
              class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs leading-relaxed text-amber-800"
            >
              Masih ada {{ unansweredCount }} soal yang belum dijawab. Soal tersebut akan tetap
              dihitung sebagai jawaban kosong jika Anda menyelesaikan kuis sekarang.
            </div>

            <p
              v-else
              class="mb-4 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-xs text-green-800"
            >
              Seluruh soal sudah dijawab. Anda dapat menyelesaikan kuis.
            </p>

            <div class="flex justify-end gap-2">
              <button
                type="button"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                @click="showFinishModal = false"
              >
                Periksa Kembali
              </button>

              <button
                type="button"
                :disabled="submitting"
                class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                @click="submitQuiz"
              >
                {{ submitting ? "Memproses..." : "Ya, Selesaikan" }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </AppLayout>
</template>
