<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";
import { Head } from "@inertiajs/vue3";

const props = defineProps({
  startToken: {
    type: String,
    required: true,
  },
  recoverVerificationId: {
    type: Number,
    default: null,
  },
});

const STORAGE_KEY = "notivra.assignment-quiz.resume-token";
const DEVICE_TOKEN_KEY = "notivra.assignment-quiz.device-token";

const session = ref(null);
const questions = ref([]);
const answers = ref({});
const currentIndex = ref(0);
const remainingSeconds = ref(0);

const loading = ref(true);
const saving = ref(false);
const completing = ref(false);
const initialized = ref(false);
const finished = ref(false);

const errorMessage = ref("");
const successMessage = ref("");
const pendingPosition = ref(null);
const showFinishConfirmation = ref(false);

let timerInterval = null;
let saveQueue = Promise.resolve();

const currentQuestion = computed(() => questions.value[currentIndex.value] ?? null);

const currentAnswer = computed(() => {
  const position = currentQuestion.value?.position;

  return position != null ? (answers.value[position] ?? "") : "";
});

const answeredCount = computed(() => Object.values(answers.value).filter(Boolean).length);

const unansweredCount = computed(() => Math.max(questions.value.length - answeredCount.value, 0));

const progressPercentage = computed(() => {
  if (!questions.value.length) return 0;

  return Math.round((answeredCount.value / questions.value.length) * 100);
});

const formattedTime = computed(() => {
  const seconds = Math.max(remainingSeconds.value, 0);
  const hours = Math.floor(seconds / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  const remainder = seconds % 60;

  return hours > 0
    ? `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}:${String(remainder).padStart(2, "0")}`
    : `${String(minutes).padStart(2, "0")}:${String(remainder).padStart(2, "0")}`;
});

const isTimeLow = computed(() => remainingSeconds.value <= 60);

const isActive = computed(() => {
  return session.value?.status === "in_progress" && !finished.value;
});

function getResumeToken() {
  return sessionStorage.getItem(STORAGE_KEY);
}

function setResumeToken(token) {
  if (token) {
    sessionStorage.setItem(STORAGE_KEY, token);
  }
}

function clearResumeToken() {
  sessionStorage.removeItem(STORAGE_KEY);
}

/**
 * HTTP helper berbasis fetch().
 * Tidak membutuhkan Axios.
 */
async function apiRequest(url, options = {}) {
  const token = options.useResumeToken === false ? null : getResumeToken();

  const headers = {
    Accept: "application/json",
    ...(options.body !== undefined ? { "Content-Type": "application/json" } : {}),
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
    ...options.headers,
  };

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

  if (csrfToken) {
    headers["X-CSRF-TOKEN"] = csrfToken;
  }

  const { useResumeToken: _ignored, ...fetchOptions } = options;

  const response = await fetch(url, {
    credentials: "same-origin",
    ...fetchOptions,
    headers,
  });

  const contentType = response.headers.get("content-type") ?? "";

  let data = {};

  if (contentType.includes("application/json")) {
    data = await response.json();
  } else {
    const text = await response.text();

    if (text) {
      data = { message: text };
    }
  }

  if (!response.ok) {
    if (response.status === 401 || response.status === 403) {
      throw new Error(data.message || "Sesi tidak valid atau akses kuis ditolak.");
    }

    if (response.status === 419) {
      throw new Error("Sesi CSRF berakhir. Muat ulang halaman dan coba kembali.");
    }

    throw new Error(data.message || `Permintaan gagal (${response.status}).`);
  }

  return data;
}

function getDeviceToken() {
  let token = localStorage.getItem(DEVICE_TOKEN_KEY);

  if (!token) {
    const bytes = crypto.getRandomValues(new Uint8Array(32));

    token = Array.from(bytes, (byte) => byte.toString(16).padStart(2, "0")).join("");

    localStorage.setItem(DEVICE_TOKEN_KEY, token);
  }

  return token;
}

async function recoverQuiz() {
  if (!props.recoverVerificationId) {
    throw new Error("ID sesi recovery tidak tersedia.");
  }

  const data = await apiRequest("/assignments/quiz/recover", {
    method: "POST",
    useResumeToken: false,
    body: JSON.stringify({
      verification_id: props.recoverVerificationId,
      device_token: getDeviceToken(),
    }),
  });

  if (!data.resume_token) {
    throw new Error("Resume token tidak diterima dari server.");
  }

  if (!data.session) {
    throw new Error("Respons server tidak berisi data sesi kuis.");
  }

  setResumeToken(data.resume_token);
  applySession(data.session);
}

function normalizeAnswers(rawAnswers) {
  const normalized = {};

  if (Array.isArray(rawAnswers)) {
    for (const item of rawAnswers) {
      const position = item.position;
      const answer = item.answer ?? item.selected_answer;

      if (position != null && answer) {
        normalized[position] = answer;
      }
    }

    return normalized;
  }

  if (rawAnswers && typeof rawAnswers === "object") {
    for (const [position, value] of Object.entries(rawAnswers)) {
      if (typeof value === "string") {
        normalized[position] = value;
      } else if (value && typeof value === "object") {
        const answer = value.answer ?? value.selected_answer;

        if (answer) {
          normalized[position] = answer;
        }
      }
    }
  }

  return normalized;
}
function applySession(payload) {
  console.log("APPLY SESSION", {
    status: payload.status,
    remaining_seconds: payload.remaining_seconds,
    started_at: payload.started_at,
    expires_at: payload.expires_at,
    completed_at: payload.completed_at,
  });

  session.value = payload;
  questions.value = Array.isArray(payload.questions) ? payload.questions : [];
  answers.value = normalizeAnswers(payload.answers);

  const completed = payload.status === "completed" || Boolean(payload.completed_at);

  finished.value = completed;

  if (completed) {
    stopTimer();
    clearResumeToken();
    return;
  }

  const savedPosition = Number(sessionStorage.getItem("notivra.assignment-quiz.position") ?? 0);

  currentIndex.value = Math.min(
    Math.max(savedPosition, 0),
    Math.max(questions.value.length - 1, 0),
  );

  if (payload.status !== "in_progress") {
    stopTimer();
    return;
  }

  const rawRemaining = payload.remaining_seconds;
  const validRemaining =
    rawRemaining !== null &&
    rawRemaining !== undefined &&
    rawRemaining !== "" &&
    Number.isFinite(Number(rawRemaining));

  if (!validRemaining) {
    stopTimer();
    remainingSeconds.value = 0;
    errorMessage.value = "Waktu sesi tidak tersedia dari server. Kuis tidak diselesaikan otomatis.";
    return;
  }

  remainingSeconds.value = Math.max(Math.floor(Number(rawRemaining)), 0);

  if (remainingSeconds.value > 0) {
    startTimer();
  } else {
    stopTimer();
    errorMessage.value = "Waktu sesi dilaporkan habis. Periksa quiz_expires_at di server.";
  }
}

async function startQuiz() {
  const data = await apiRequest("/assignments/quiz/start", {
    method: "POST",
    useResumeToken: false,
    body: JSON.stringify({
      start_token: props.startToken,
      device_token: getDeviceToken(),
    }),
  });

  if (data.resume_token) {
    setResumeToken(data.resume_token);
  }

  if (!data.session) {
    throw new Error("Respons server tidak berisi data sesi kuis.");
  }

  applySession(data.session);
}

async function resumeQuiz() {
  const data = await apiRequest("/assignments/quiz/resume", {
    method: "POST",
  });

  if (!data.session) {
    throw new Error("Server tidak mengembalikan data sesi kuis.");
  }

  applySession(data.session);
}

async function loadSession() {
  const data = await apiRequest("/assignments/quiz/session");

  if (!data.session) {
    throw new Error("Server tidak mengembalikan data sesi kuis.");
  }

  applySession(data.session);
}

async function initializeQuiz() {
  loading.value = true;
  errorMessage.value = "";

  try {
    // Jalur recovery dari tombol Lanjutkan Kuis.
    if (props.recoverVerificationId) {
      await recoverQuiz();
      initialized.value = true;
      return;
    }

    // Jalur resume normal menggunakan sessionStorage.
    if (getResumeToken()) {
      try {
        await resumeQuiz();
        initialized.value = true;
        return;
      } catch (error) {
        clearResumeToken();

        if (error.message.includes("ditolak") || error.message.includes("tidak valid")) {
          throw error;
        }
      }
    }

    // Jalur memulai kuis baru.
    await startQuiz();
    initialized.value = true;
  } catch (error) {
    errorMessage.value = error.message || "Gagal memuat kuis.";
    initialized.value = false;
  } finally {
    loading.value = false;
  }
}

function selectAnswer(option) {
  if (!isActive.value || saving.value || completing.value) {
    return;
  }

  const question = currentQuestion.value;

  if (!question) return;

  const position = question.position;

  // Simpan pilihan di UI terlebih dahulu.
  answers.value = {
    ...answers.value,
    [position]: option,
  };

  errorMessage.value = "";
  successMessage.value = "";

  // Antrekan penyimpanan agar jawaban yang dikirim berurutan.
  saveQueue = saveQueue.catch(() => {}).then(() => saveAnswer(position, option));
}

async function saveAnswer(position, answer) {
  if (!isActive.value) return;

  pendingPosition.value = position;
  saving.value = true;
  errorMessage.value = "";

  try {
    const data = await apiRequest(`/assignments/quiz/answers/${position}`, {
      method: "PUT",
      body: JSON.stringify({ answer }),
    });

    if (data.session) {
      session.value = data.session;
      answers.value = normalizeAnswers(data.session.answers);

      const seconds = Number(data.session.remaining_seconds);

      if (data.session.status === "in_progress" && Number.isFinite(seconds) && seconds > 0) {
        remainingSeconds.value = seconds;

        if (timerInterval === null) {
          startTimer();
        }
      }
    }

    successMessage.value = "Jawaban berhasil disimpan.";
  } catch (error) {
    errorMessage.value = error.message || "Gagal menyimpan jawaban.";

    // Jangan menghapus pilihan lokal.
    // Pengguna dapat mencoba menyimpan kembali.
  } finally {
    pendingPosition.value = null;
    saving.value = false;
  }
}

async function retrySaveAnswer() {
  const question = currentQuestion.value;

  if (!question) return;

  const answer = answers.value[question.position];

  if (!answer) return;

  saveQueue = saveQueue.catch(() => {}).then(() => saveAnswer(question.position, answer));

  await saveQueue;
}

function goToQuestion(index) {
  if (index < 0 || index >= questions.value.length) {
    return;
  }

  currentIndex.value = index;

  sessionStorage.setItem("notivra.assignment-quiz.position", String(index));

  errorMessage.value = "";
  successMessage.value = "";
}

function previousQuestion() {
  goToQuestion(currentIndex.value - 1);
}

function nextQuestion() {
  goToQuestion(currentIndex.value + 1);
}

function startTimer() {
  stopTimer();

  timerInterval = setInterval(() => {
    if (remainingSeconds.value > 0) {
      remainingSeconds.value -= 1;
    }

    if (remainingSeconds.value <= 0) {
      remainingSeconds.value = 0;
      stopTimer();

      errorMessage.value =
        "Hitungan waktu di halaman telah mencapai nol. Status sesi harus diverifikasi oleh server.";
    }
  }, 1000);
}

function stopTimer() {
  if (timerInterval !== null) {
    clearInterval(timerInterval);
    timerInterval = null;
  }
}

async function completeQuiz(autoSubmit = false) {
  if (completing.value || finished.value) return;

  if (!autoSubmit && !isActive.value) return;

  completing.value = true;
  showFinishConfirmation.value = false;
  errorMessage.value = "";

  try {
    // Tunggu seluruh operasi penyimpanan jawaban yang sudah antre.
    await saveQueue;

    const data = await apiRequest("/assignments/quiz/complete", {
      method: "POST",
    });

    finished.value = true;
    stopTimer();
    clearResumeToken();

    session.value = {
      ...session.value,
      status: data.status ?? "completed",
      completed_at: data.completed_at ?? new Date().toISOString(),
    };

    successMessage.value = data.message || "Kuis berhasil diselesaikan.";
  } catch (error) {
    errorMessage.value = error.message || "Gagal menyelesaikan kuis.";

    // Jika waktu habis, pengguna tidak boleh melanjutkan
    // mengerjakan. Namun, permintaan penyelesaian dapat dicoba lagi.
  } finally {
    completing.value = false;
  }
}

function requestFinish() {
  showFinishConfirmation.value = true;
}

function cancelFinish() {
  showFinishConfirmation.value = false;
}

function optionText(question, option) {
  const key = `option_${option.toLowerCase()}`;
  return question?.[key] ?? "";
}

onMounted(() => {
  void initializeQuiz();
});

onUnmounted(() => {
  stopTimer();
});
</script>

<template>
  <Head title="Kuis Verifikasi" />

  <main class="min-h-screen bg-slate-50 text-slate-800">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <!-- Header -->
      <header
        class="mb-6 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between"
      >
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-600">
            Notivra · Verifikasi
          </p>

          <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Kuis Verifikasi</h1>

          <p class="mt-1 text-sm text-slate-500">
            Jawab seluruh pertanyaan sebelum waktu berakhir.
          </p>
          <p class="mt-2 font-semibold">
            <span class="text-sm font-normal text-muted-foreground">Nama:</span>
            {{ session?.student?.name ?? "Nama mahasiswa tidak tersedia" }}
            <span class="text-sm font-normal text-muted-foreground"
              >| NPM: {{ session?.student?.npm ?? "-" }}</span
            >
          </p>
        </div>

        <div
          v-if="initialized && !finished"
          class="flex items-center gap-3 self-start rounded-xl border px-4 py-3 sm:self-auto"
          :class="
            isTimeLow
              ? 'border-red-200 bg-red-50 text-red-700'
              : 'border-slate-200 bg-slate-50 text-slate-700'
          "
        >
          <svg
            class="h-5 w-5 shrink-0"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            aria-hidden="true"
          >
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v5l3 2" />
          </svg>

          <div>
            <p class="text-xs font-medium">Sisa waktu</p>

            <p class="font-mono text-xl font-bold tabular-nums" role="timer" aria-live="off">
              {{ formattedTime }}
            </p>
          </div>
        </div>
      </header>

      <!-- Loading -->
      <section
        v-if="loading"
        class="rounded-2xl border border-slate-200 bg-white px-6 py-16 text-center shadow-sm"
        aria-live="polite"
      >
        <div
          class="mx-auto h-9 w-9 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"
          aria-hidden="true"
        />

        <p class="mt-4 font-medium text-slate-800">Memuat sesi kuis...</p>

        <p class="mt-1 text-sm text-slate-500">Mohon tunggu sementara data diverifikasi.</p>
      </section>

      <!-- Error saat inisialisasi -->
      <section
        v-else-if="!initialized"
        class="rounded-2xl border border-red-200 bg-white p-6 shadow-sm"
      >
        <div class="flex min-w-lg gap-3">
          <div class="rounded-lg bg-red-50 p-2 text-red-600">
            <svg
              class="h-5 w-5"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.8"
              aria-hidden="true"
            >
              <circle cx="12" cy="12" r="9" />
              <path d="M12 8v5m0 3h.01" />
            </svg>
          </div>

          <div class="min-w-0 flex-1">
            <h2 class="font-semibold text-slate-900">Kuis tidak dapat dimuat</h2>

            <p class="mt-1 text-sm text-slate-600">
              {{ errorMessage }}
            </p>

            <button
              type="button"
              class="mt-4 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
              @click="initializeQuiz"
            >
              Coba lagi
            </button>
          </div>
        </div>
      </section>

      <!-- Sesi selesai -->
      <section
        v-else-if="finished"
        class="rounded-2xl border border-emerald-200 bg-white p-8 text-center shadow-sm sm:p-12"
      >
        <div
          class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50 text-emerald-600"
        >
          <svg
            class="h-8 w-8"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            aria-hidden="true"
          >
            <path d="m5 12 4 4L19 6" />
          </svg>
        </div>

        <h2 class="mt-5 text-2xl font-bold text-slate-900">Kuis telah diselesaikan</h2>

        <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-600">
          Sesi kuis Anda telah berakhir. Jawaban yang tersimpan telah dikirim untuk diproses oleh
          sistem.
        </p>

        <div class="mx-auto mt-6 max-w-sm rounded-xl bg-slate-50 p-5 text-left">
          <div class="flex items-center justify-between gap-4 py-2">
            <span class="text-sm text-slate-500"> Total pertanyaan </span>

            <span class="font-semibold text-slate-900">
              {{ questions.length }}
            </span>
          </div>

          <div class="flex items-center justify-between gap-4 py-2">
            <span class="text-sm text-slate-500"> Jawaban tersimpan di halaman </span>

            <span class="font-semibold text-slate-900">
              {{ answeredCount }}
            </span>
          </div>

          <div class="flex items-center justify-between gap-4 py-2">
            <span class="text-sm text-slate-500"> Status </span>

            <span
              class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700"
            >
              Selesai
            </span>
          </div>
        </div>

        <p v-if="successMessage" class="mt-5 text-sm text-emerald-700" role="status">
          {{ successMessage }}
        </p>
      </section>

      <!-- Isi kuis -->
      <template v-else>
        <!-- Informasi progres -->
        <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
              <p class="text-sm font-semibold text-slate-900">Progres pengerjaan</p>

              <p class="mt-1 text-sm text-slate-500">
                {{ answeredCount }} dari {{ questions.length }}
                pertanyaan dijawab
              </p>
            </div>

            <span class="text-sm font-semibold tabular-nums text-indigo-700">
              {{ progressPercentage }}%
            </span>
          </div>

          <div
            class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100"
            role="progressbar"
            :aria-valuenow="progressPercentage"
            aria-valuemin="0"
            aria-valuemax="100"
            aria-label="Progres pengerjaan kuis"
          >
            <div
              class="h-full rounded-full bg-indigo-600 transition-all duration-300"
              :style="{ width: `${progressPercentage}%` }"
            />
          </div>

          <div class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-xs text-slate-500">
            <span class="inline-flex items-center gap-2">
              <span class="h-2.5 w-2.5 rounded-full bg-indigo-600" />
              Sudah dijawab: {{ answeredCount }}
            </span>

            <span class="inline-flex items-center gap-2">
              <span class="h-2.5 w-2.5 rounded-full bg-slate-200" />
              Belum dijawab: {{ unansweredCount }}
            </span>
          </div>
        </section>

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_280px]">
          <!-- Pertanyaan -->
          <section class="min-w-0 rounded-2xl border border-slate-200 bg-white shadow-sm">
            <template v-if="currentQuestion">
              <div class="border-b border-slate-100 px-5 py-5 sm:px-7">
                <div class="flex flex-wrap items-center justify-between gap-3">
                  <span class="text-xs font-semibold uppercase tracking-[0.14em] text-indigo-600">
                    Pertanyaan {{ currentIndex + 1 }} dari {{ questions.length }}
                  </span>

                  <span
                    v-if="answers[currentQuestion.position]"
                    class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700"
                  >
                    Sudah dijawab
                  </span>

                  <span
                    v-else
                    class="rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700"
                  >
                    Belum dijawab
                  </span>
                </div>

                <h2
                  class="mt-4 whitespace-pre-line text-lg font-semibold leading-8 text-slate-900 sm:text-xl"
                >
                  {{ currentQuestion.question }}
                </h2>
              </div>

              <div class="space-y-3 p-5 sm:p-7">
                <button
                  v-for="option in ['A', 'B', 'C', 'D']"
                  :key="option"
                  type="button"
                  :disabled="!isActive || saving || completing"
                  class="group flex w-full items-start gap-4 rounded-xl border p-4 text-left transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-70"
                  :class="
                    currentAnswer === option
                      ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500'
                      : 'border-slate-200 bg-white hover:border-indigo-300 hover:bg-slate-50'
                  "
                  :aria-pressed="currentAnswer === option"
                  @click="selectAnswer(option)"
                >
                  <span
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border text-sm font-bold transition"
                    :class="
                      currentAnswer === option
                        ? 'border-indigo-600 bg-indigo-600 text-white'
                        : 'border-slate-200 bg-white text-slate-600 group-hover:border-indigo-300'
                    "
                  >
                    {{ option }}
                  </span>

                  <span
                    class="min-w-0 flex-1 whitespace-pre-line pt-1 text-sm leading-6 text-slate-700"
                  >
                    {{ optionText(currentQuestion, option) }}
                  </span>

                  <svg
                    v-if="currentAnswer === option"
                    class="mt-1 h-5 w-5 shrink-0 text-indigo-600"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    aria-hidden="true"
                  >
                    <path d="m5 12 4 4L19 6" />
                  </svg>
                </button>

                <p v-if="saving" class="pt-2 text-sm text-slate-500" role="status">
                  Menyimpan jawaban...
                </p>

                <p v-else-if="successMessage" class="pt-2 text-sm text-emerald-700" role="status">
                  {{ successMessage }}
                </p>

                <div
                  v-if="errorMessage"
                  class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4"
                  role="alert"
                >
                  <p class="text-sm text-red-700">
                    {{ errorMessage }}
                  </p>

                  <button
                    v-if="currentAnswer"
                    type="button"
                    class="mt-2 text-sm font-semibold text-red-800 underline underline-offset-2"
                    :disabled="saving || completing"
                    @click="retrySaveAnswer"
                  >
                    Coba simpan jawaban lagi
                  </button>
                </div>
              </div>

              <div
                class="flex items-center justify-between gap-3 border-t border-slate-100 px-5 py-4 sm:px-7"
              >
                <button
                  type="button"
                  class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                  :disabled="currentIndex === 0 || saving || completing"
                  @click="previousQuestion"
                >
                  Sebelumnya
                </button>

                <button
                  v-if="currentIndex < questions.length - 1"
                  type="button"
                  class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                  :disabled="saving || completing"
                  @click="nextQuestion"
                >
                  Berikutnya
                </button>

                <button
                  v-else
                  type="button"
                  class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                  :disabled="saving || completing"
                  @click="requestFinish"
                >
                  Selesaikan kuis
                </button>
              </div>
            </template>

            <div v-else class="p-8 text-center">
              <h2 class="font-semibold text-slate-900">Belum ada pertanyaan</h2>

              <p class="mt-2 text-sm text-slate-500">
                Sesi tidak memiliki pertanyaan yang dapat ditampilkan.
              </p>
            </div>
          </section>

          <!-- Navigasi pertanyaan -->
          <aside
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:sticky lg:top-6"
          >
            <h2 class="font-semibold text-slate-900">Navigasi pertanyaan</h2>

            <p class="mt-1 text-sm text-slate-500">Pilih nomor untuk berpindah.</p>

            <div class="mt-5 grid grid-cols-5 gap-2">
              <button
                v-for="(question, index) in questions"
                :key="question.position"
                type="button"
                class="flex aspect-square items-center justify-center rounded-lg border text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                :class="[
                  currentIndex === index
                    ? 'border-indigo-600 bg-indigo-600 text-white'
                    : answers[question.position]
                      ? 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                      : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50',
                ]"
                :aria-label="`Pertanyaan ${index + 1}${answers[question.position] ? ', sudah dijawab' : ', belum dijawab'}`"
                :aria-current="currentIndex === index ? 'step' : undefined"
                @click="goToQuestion(index)"
              >
                {{ index + 1 }}
              </button>
            </div>

            <div class="mt-5 space-y-3 border-t border-slate-100 pt-4">
              <div class="flex items-center justify-between text-sm">
                <span class="text-slate-500"> Sudah dijawab </span>

                <span class="font-semibold text-emerald-700">
                  {{ answeredCount }}
                </span>
              </div>

              <div class="flex items-center justify-between text-sm">
                <span class="text-slate-500"> Belum dijawab </span>

                <span class="font-semibold text-amber-700">
                  {{ unansweredCount }}
                </span>
              </div>
            </div>

            <button
              type="button"
              class="mt-6 w-full rounded-lg bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
              :disabled="saving || completing || !questions.length"
              @click="requestFinish"
            >
              Selesaikan kuis
            </button>

            <p class="mt-3 text-xs leading-5 text-slate-500">
              Pastikan jawaban tersimpan sebelum menyelesaikan kuis.
            </p>
          </aside>
        </div>
      </template>
    </div>

    <!-- Dialog konfirmasi -->
    <Teleport to="body">
      <div
        v-if="showFinishConfirmation && !finished"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4"
        @keydown.esc="cancelFinish"
      >
        <section
          class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl"
          role="dialog"
          aria-modal="true"
          aria-labelledby="finish-dialog-title"
        >
          <h2 id="finish-dialog-title" class="text-lg font-bold text-slate-900">
            Selesaikan kuis?
          </h2>

          <p class="mt-2 text-sm leading-6 text-slate-600">
            Anda telah menjawab
            <strong>{{ answeredCount }}</strong>
            dari
            <strong>{{ questions.length }}</strong>
            pertanyaan.
          </p>

          <p
            v-if="unansweredCount > 0"
            class="mt-3 rounded-lg bg-amber-50 p-3 text-sm leading-5 text-amber-800"
          >
            Masih ada {{ unansweredCount }} pertanyaan yang belum dijawab. Anda tetap dapat
            menyelesaikan kuis jika menginginkannya.
          </p>

          <p class="mt-3 text-sm leading-6 text-slate-600">
            Setelah diselesaikan, Anda tidak dapat melanjutkan sesi ini.
          </p>

          <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <button
              type="button"
              class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
              @click="cancelFinish"
            >
              Kembali ke kuis
            </button>

            <button
              type="button"
              class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
              :disabled="completing || saving"
              @click="completeQuiz(false)"
            >
              {{ completing ? "Menyelesaikan..." : "Ya, selesaikan" }}
            </button>
          </div>
        </section>
      </div>
    </Teleport>
  </main>
</template>
