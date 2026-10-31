<script setup>
import { computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";

const props = defineProps({
  initialLocale: String,
});

const page = usePage();
const currentLang = computed(() => {
  return page?.props?.locale || props.initialLocale || "en";
});

const switchLocale = (lang) => {
  if (lang === currentLang.value) return;

  document.cookie = `locale=${lang}; path=/; max-age=31536000; SameSite=Lax`;

  const isInertia = document.getElementById("app") !== null;

  if (isInertia) {
    router.reload({ preserveScroll: true });
  } else {
    window.location.reload();
  }
};

const nextLang = computed(() => (currentLang.value === "en" ? "id" : "en"));
</script>

<template>
  <button
    @click="switchLocale(nextLang)"
    :aria-label="`Switch language to ${nextLang === 'id' ? 'Bahasa Indonesia' : 'English'}`"
    :title="nextLang === 'id' ? 'Bahasa Indonesia' : 'English'"
    class="group flex cursor-pointer items-center gap-2 rounded-md border border-slate-300 px-2 py-1 transition hover:bg-slate-100"
  >
    <span class="flex items-center gap-1.5 text-sm font-semibold text-slate-700">
      <!-- Indonesia -->
      <svg
        v-if="nextLang === 'id'"
        viewBox="0 0 60 40"
        xmlns="http://www.w3.org/2000/svg"
        class="h-4 w-6 shrink-0"
        aria-hidden="true"
      >
        <defs>
          <filter id="wave-id" x="-15%" y="-15%" width="130%" height="130%">
            <feTurbulence
              type="fractalNoise"
              baseFrequency="0.025 0.06"
              numOctaves="2"
              seed="2"
              result="noise"
            >
              <animate
                attributeName="baseFrequency"
                dur="3s"
                values="0.025 0.06;0.035 0.09;0.025 0.06"
                repeatCount="indefinite"
              />
            </feTurbulence>

            <feDisplacementMap
              in="SourceGraphic"
              in2="noise"
              scale="3"
              xChannelSelector="R"
              yChannelSelector="G"
            />
          </filter>

          <linearGradient id="shade-id" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0%" stop-color="#ffffff" stop-opacity="0.25" />
            <stop offset="45%" stop-color="#ffffff" stop-opacity="0" />
            <stop offset="100%" stop-color="#000000" stop-opacity="0.18" />
          </linearGradient>

          <clipPath id="clip-id">
            <rect width="60" height="40" rx="2" />
          </clipPath>
        </defs>

        <g clip-path="url(#clip-id)" filter="url(#wave-id)">
          <rect width="60" height="20" fill="#e00000" />
          <rect y="20" width="60" height="20" fill="#ffffff" />
          <rect width="60" height="40" fill="url(#shade-id)" />
        </g>
      </svg>

      <!-- United Kingdom -->
      <svg
        v-else
        viewBox="0 0 60 40"
        xmlns="http://www.w3.org/2000/svg"
        class="h-4 w-6 shrink-0"
        aria-hidden="true"
      >
        <defs>
          <filter id="wave-en" x="-15%" y="-15%" width="130%" height="130%">
            <feTurbulence
              type="fractalNoise"
              baseFrequency="0.025 0.06"
              numOctaves="2"
              seed="2"
              result="noise"
            >
              <animate
                attributeName="baseFrequency"
                dur="3s"
                values="0.025 0.06;0.035 0.09;0.025 0.06"
                repeatCount="indefinite"
              />
            </feTurbulence>

            <feDisplacementMap
              in="SourceGraphic"
              in2="noise"
              scale="3"
              xChannelSelector="R"
              yChannelSelector="G"
            />
          </filter>

          <linearGradient id="shade-en" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0%" stop-color="#ffffff" stop-opacity="0.25" />
            <stop offset="45%" stop-color="#ffffff" stop-opacity="0" />
            <stop offset="100%" stop-color="#000000" stop-opacity="0.18" />
          </linearGradient>

          <clipPath id="clip-en">
            <rect width="60" height="40" rx="2" />
          </clipPath>
        </defs>

        <g clip-path="url(#clip-en)" filter="url(#wave-en)">
          <rect width="60" height="40" fill="#012169" />

          <path d="M0 0 L6.7 0 L60 33.5 L60 40 L53.3 40 L0 6.5 Z" fill="#ffffff" />
          <path d="M60 0 L53.3 0 L0 33.5 L0 40 L6.7 40 L60 6.5 Z" fill="#ffffff" />

          <path d="M0 0 L3.7 0 L60 35.2 L60 40 L56.3 40 L0 4.8 Z" fill="#c8102e" />
          <path d="M60 0 L56.3 0 L0 35.2 L0 40 L3.7 40 L60 4.8 Z" fill="#c8102e" />

          <path d="M25 0 H35 V15 H60 V25 H35 V40 H25 V25 H0 V15 H25 Z" fill="#ffffff" />

          <path d="M27 0 H33 V17 H60 V23 H33 V40 H27 V23 H0 V17 H27 Z" fill="#c8102e" />

          <rect width="60" height="40" fill="url(#shade-en)" />
        </g>
      </svg>
      <span class="uppercase">
        {{ nextLang }}
      </span>
    </span>
  </button>
</template>

<style scoped>
/* Tambahkan styling tambahan jika diperlukan */
</style>
