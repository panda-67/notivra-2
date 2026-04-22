import '../css/app.css'; // Kita tetap pakai Tailwind yang sama
import { createApp } from 'vue';
import BlogSearch from './Components/BlogSearch.vue';

const app = createApp({});

app.component('blog-search', BlogSearch);

app.mount('#search-root');
