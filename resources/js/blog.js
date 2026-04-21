import '../css/app.css'; // Kita tetap pakai Tailwind yang sama
import { createApp } from 'vue';

// Contoh: Jika nanti kamu buat komponen pencarian
// import BlogSearch from './components/BlogSearch.vue';

const app = createApp({});

// app.component('blog-search', BlogSearch);

app.mount('#blog');
