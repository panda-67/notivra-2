<script setup>
import { computed, ref, watch } from "vue";
import { Head } from "@inertiajs/vue3";
import students from "@/Data/recap/statistika_mtk.json";

const search = ref("");
const selectedGrade = ref("all");
const sortBy = ref("original");
const perPage = ref(10);
const currentPage = ref(1);

const gradeOptions = ["A", "AB", "B", "BC", "C", "D", "E"];

const filteredStudents = computed(() => {
    const keyword = search.value.trim().toLocaleLowerCase("id-ID");

    const result = students.filter((student) => {
        const matchesSearch =
            student.nama.toLocaleLowerCase("id-ID").includes(keyword) || student.nim.includes(keyword);

        const matchesGrade =
            selectedGrade.value === "all" || student.nilai_huruf === selectedGrade.value;

        return matchesSearch && matchesGrade;
    });

    if (sortBy.value === "nama-asc") {
        result.sort((a, b) => a.nama.localeCompare(b.nama, "id"));
    } else if (sortBy.value === "nama-desc") {
        result.sort((a, b) => b.nama.localeCompare(a.nama, "id"));
    } else if (sortBy.value === "nilai-desc") {
        result.sort((a, b) => b.nilai - a.nilai || a.nama.localeCompare(b.nama, "id"));
    } else if (sortBy.value === "nilai-asc") {
        result.sort((a, b) => a.nilai - b.nilai || a.nama.localeCompare(b.nama, "id"));
    } else if (sortBy.value === "nim-asc") {
        result.sort((a, b) => a.nim.localeCompare(b.nim, "id", { numeric: true }));
    }

    return result;
});

const totalPages = computed(() =>
    Math.max(1, Math.ceil(filteredStudents.value.length / perPage.value)),
);

const paginatedStudents = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    return filteredStudents.value.slice(start, start + perPage.value);
});

const firstRow = computed(() =>
    filteredStudents.value.length === 0 ? 0 : (currentPage.value - 1) * perPage.value + 1,
);

const lastRow = computed(() =>
    Math.min(currentPage.value * perPage.value, filteredStudents.value.length),
);

watch([search, selectedGrade, sortBy, perPage], () => {
    currentPage.value = 1;
});

watch(totalPages, (total) => {
    if (currentPage.value > total) currentPage.value = total;
});

function formatScore(value) {
    return new Intl.NumberFormat("id-ID", {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(value);
}

function resetFilters() {
    search.value = "";
    selectedGrade.value = "all";
    sortBy.value = "original";
    perPage.value = 10;
    currentPage.value = 1;
}
</script>

<template>

    <Head title="Rekap Nilai Kuis Kelompok" />

    <main class="min-h-screen bg-slate-50 text-slate-800">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <header class="mb-8 border-b border-slate-200 pb-6">
                <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-indigo-600">
                    Notivra · Publikasi Akademik
                </p>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">
                            Rekap Nilai Kuis Kelompok
                        </h1>
                        <p class="mt-2 text-sm text-slate-600">Statistika Inferensial · FKIP</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                        <p class="text-xs font-medium text-slate-500">Jumlah mahasiswa</p>
                        <p class="mt-1 text-2xl font-bold tabular-nums text-slate-950">
                            {{ students.length }}
                        </p>
                    </div>
                </div>
            </header>

            <section class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-semibold text-slate-900">Daftar nilai mahasiswa</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Cari mahasiswa, saring nilai huruf, atau ubah urutan data.
                        </p>
                    </div>
                    <button type="button" class="self-start text-sm font-medium text-indigo-700 hover:text-indigo-900"
                        @click="resetFilters">
                        Reset filter
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <label class="block xl:col-span-2">
                        <span class="mb-1.5 block text-xs font-semibold text-slate-600"> Pencarian </span>
                        <input v-model="search" type="search" autocomplete="off" placeholder="Cari nama atau NIM..."
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100" />
                    </label>

                    <label class="block">
                        <span class="mb-1.5 block text-xs font-semibold text-slate-600"> Nilai huruf </span>
                        <select v-model="selectedGrade"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                            <option value="all">Semua nilai</option>
                            <option v-for="grade in gradeOptions" :key="grade" :value="grade">
                                {{ grade }}
                            </option>
                        </select>
                    </label>

                    <label class="block">
                        <span class="mb-1.5 block text-xs font-semibold text-slate-600"> Urutkan </span>
                        <select v-model="sortBy"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                            <option value="original">Urutkan</option>
                            <option value="nama-asc">Nama A–Z</option>
                            <option value="nama-desc">Nama Z–A</option>
                            <option value="nim-asc">NIM</option>
                            <option value="nilai-desc">Nilai tertinggi</option>
                            <option value="nilai-asc">Nilai terendah</option>
                        </select>
                    </label>
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div
                    class="flex flex-col gap-2 border-b border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                    <p class="text-sm text-slate-600">
                        Menampilkan
                        <span class="font-semibold text-slate-900">{{ firstRow }}–{{ lastRow }}</span>
                        dari
                        <span class="font-semibold text-slate-900">{{ filteredStudents.length }}</span>
                        mahasiswa
                    </p>
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        Baris per halaman
                        <select v-model.number="perPage"
                            class="rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-800 outline-none focus:border-indigo-500">
                            <option :value="10">10</option>
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                        </select>
                    </label>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th scope="col" class="w-16 px-4 py-3 text-center">No.</th>
                                <th scope="col" class="min-w-56 px-4 py-3">Nama mahasiswa</th>
                                <th scope="col" class="min-w-44 px-4 py-3">NIM</th>
                                <th scope="col" class="px-4 py-3 text-right">Kuis</th>
                                <th scope="col" class="px-4 py-3 text-right">Tugas</th>
                                <th scope="col" class="px-4 py-3 text-right">Nilai</th>
                                <th scope="col" class="px-4 py-3 text-center">Huruf</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(student, index) in paginatedStudents" :key="student.nim"
                                class="transition hover:bg-indigo-50/40">
                                <td class="px-4 py-3 text-center tabular-nums text-slate-400">
                                    {{ (currentPage - 1) * perPage + index + 1 }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-800">
                                    {{ student.nama }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-slate-600">
                                    {{ student.nim }}
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums text-slate-700">
                                    {{ formatScore(student.kuis) }}
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums text-slate-700">
                                    {{ formatScore(student.tugas) }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold tabular-nums text-slate-900">
                                    {{ formatScore(student.nilai) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="inline-flex min-w-9 justify-center rounded-md bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700">
                                        {{ student.nilai_huruf }}
                                    </span>
                                </td>
                            </tr>

                            <tr v-if="paginatedStudents.length === 0">
                                <td colspan="7" class="px-4 py-12 text-center">
                                    <p class="font-medium text-slate-700">Data tidak ditemukan</p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Ubah kata kunci pencarian atau filter nilai huruf.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <footer
                    class="flex flex-col gap-3 border-t border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                    <p class="text-xs text-slate-500">Halaman {{ currentPage }} dari {{ totalPages }}</p>
                    <div class="flex items-center gap-2">
                        <button type="button" :disabled="currentPage <= 1"
                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                            @click="currentPage--">
                            Sebelumnya
                        </button>
                        <button type="button" :disabled="currentPage >= totalPages"
                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                            @click="currentPage++">
                            Berikutnya
                        </button>
                    </div>
                </footer>
            </section>

            <p class="mt-5 text-center text-xs leading-relaxed text-slate-500">
                Rekap nilai ditampilkan sesuai data hasil pengolahan. Jika terdapat ketidaksesuaian, silakan
                hubungi pengampu mata kuliah.
            </p>
        </div>
    </main>
</template>
