import { defineConfig, loadEnv } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import inertia from "@inertiajs/vite";
import vue from "@vitejs/plugin-vue";
import { resolve } from "node:path";
import path from "path";

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), "");
    const isProduction = env.APP_ENV === "production";

    const publicDirectory = isProduction ? "../../public_html/notivra" : "../public_html";
    const buildDirectory = isProduction ? "../../public_html/notivra/build" : "../public_html/build";

    return {
        plugins: [
            laravel({
                input: ["resources/css/app.css", "resources/js/app.js", "resources/js/blog.js"],
                hotFile: `${publicDirectory}/hot`,
                publicDirectory,
                refresh: ["resources/views/**/*.blade.php", "routes/**/*.php"],
            }),
            tailwindcss(),
            inertia({ ssr: false }),
            vue({
                template: {
                    transformAssetUrls: { base: null, includeAbsolute: false },
                },
            }),
        ],

        resolve: {
            alias: {
                "ziggy-js": resolve(__dirname, "vendor/tightenco/ziggy"),
                vue: "vue/dist/vue.esm-bundler.js",
            },
        },

        build: {
            outDir: path.resolve(process.cwd(), buildDirectory),
            emptyOutDir: true,
        },

        server: {
            hmr: { host: "localhost" },
            watch: { usePolling: true, ignored: ["**/storage/framework/views/**"] },
        },
    };
});
