<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useLanguage } from '@/Composables/useLanguage';

const { locale } = useLanguage();
const services = computed(() => usePage().props.footerServices);

</script>

<template>
    <footer class="bg-[#0f172a] text-slate-300 pt-16 pb-8 border-t border-slate-800">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">

                <div class="col-span-1">
                    <h3 class="text-white text-xl font-bold mb-4">Notivra<span class="text-blue-500">.</span></h3>
                    <p class="text-sm leading-relaxed">
                        <template v-if="locale === 'id'">
                            Penyedia layanan pendukung akademik terpercaya di Banda Aceh.
                            Kami membantu mahasiswa dan peneliti mencapai standar akademik tertinggi.
                        </template>
                        <template v-else>
                            Trusted academic support service provider in Banda Aceh.
                            We assist students and researchers in achieving the highest academic standards.
                        </template>
                    </p>
                </div>

                <div>
                    <h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">
                        <span v-if="locale === 'id'">Layanan Kami</span>
                        <span v-else>Our Services</span>
                    </h4>
                    <ul class="space-y-2 text-sm">
                        <li v-for="service in services" :key="service.id">
                            <Link :href="route('services.show', service.id)" class="hover:text-blue-400 transition">
                                {{ service.title[locale] }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">
                        <span v-if="locale === 'id'">Kontak</span>
                        <span v-else>Contact</span>
                    </h4>
                    <div class="space-y-4 text-sm">
                        <p class="flex items-start gap-3">
                            <span class="text-blue-400">📧</span>
                            <span>info@notivra.com</span>
                        </p>
                        <p class="flex items-start gap-3 leading-relaxed">
                            <span class="text-blue-400">📍</span>
                            <span>Jl. Tgk. Syech Abdul Rauf, Kopelma Darussalam, Syiah Kuala, Banda Aceh.</span>
                        </p>
                    </div>
                </div>

                <div class="col-span-1">
                    <div
                        class="rounded-xl overflow-hidden grayscale opacity-80 hover:grayscale-0 hover:opacity-100 transition duration-500 border border-slate-700 h-32 md:h-full min-h-37.5">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m10!1m8!1m3!1d1001.7996876667295!2d95.367241!3d5.566319!3m2!1i1024!2i768!4f13.1!5e1!3m2!1sid!2sid!4v1777295534609!5m2!1sid!2sid"
                            width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>

            <div
                class="pt-8 border-t border-slate-800 flex flex-col md:flex-row justify-between items-center gap-4 text-[12px]">
                <p>&copy; 2026 Notivra. All rights reserved.</p>
                <div class="flex gap-6">
                    <a href="/" class="hover:text-white">Home</a>
                    <Link href="/dashboard" class="hover:text-white">Dashboard</Link>
                    <Link href="/services" class="hover:text-white">Services</Link>
                    <Link href="/about" class="hover:text-white">About Us</Link>
                    <a href="mailto:info@notivra.com" class="hover:text-white">Contact</a>
                </div>
            </div>
        </div>
    </footer>
</template>
