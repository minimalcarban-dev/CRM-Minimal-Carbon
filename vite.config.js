import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import vue from "@vitejs/plugin-vue";
import path from "path";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    server: {
        host: true, // listen on all addresses so other devices can reach Vite
        port: Number(process.env.VITE_DEV_PORT || 5173),
        strictPort: true,
        hmr: {
            host: process.env.VITE_HMR_HOST || "localhost",
            port: Number(process.env.VITE_DEV_PORT || 5173),
            protocol: process.env.VITE_HMR_PROTOCOL || "ws",
        },
        origin: process.env.VITE_DEV_ORIGIN || undefined,
    },
    resolve: {
        alias: {
            vue: "vue/dist/vue.esm-bundler.js",
            "@": path.resolve(__dirname, "resources/js"),
        },
    },
    optimizeDeps: {
        include: ["lodash", "axios", "vue", "date-fns", "pusher-js"],
    },
});
