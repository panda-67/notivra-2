import '../css/app.css'; // Kita tetap pakai Tailwind yang sama
import { createApp } from 'vue';
import BlogSearch from './Components/BlogSearch.vue';
import ShareFooter from './Components/ShareFooter.vue';
import NewsletterCard from './Components/NewsletterCard.vue';
import LangSwitcher from './Components/LangSwitcher.vue';

// Instance untuk Search
const searchApp = createApp({});
searchApp.component('blog-search', BlogSearch);
searchApp.mount('#search-root');

// Instance untuk Share Footer
const shareApp = createApp({});
shareApp.component('share-footer', ShareFooter);
shareApp.mount('#share-root'); // Gunakan ID unik di sini

// Instance untuk Newsletter
const newsApp = createApp({});
newsApp.component('newsletter-card', NewsletterCard);
newsApp.mount('#newsletter-root');

const langApp = createApp({});
langApp.component('lang-switcher', LangSwitcher);
langApp.mount('#lang-switcher-root');
