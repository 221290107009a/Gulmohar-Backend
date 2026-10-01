import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue2";
import glob from "glob";
import copy from "rollup-plugin-copy";
import path from "path";
import autoprefixer from "autoprefixer";
import postcssRTLCSS from "postcss-rtlcss";

const FLEETCART_VERSION = "4.2.3";
const ASSET_PATHS = [
    "modules/*/Resources/assets/*/sass/main.scss",
    "modules/*/Resources/assets/*/js/main.js",
    "modules/*/Resources/assets/*/js/create.js",
    "modules/*/Resources/assets/*/js/edit.js",
    "modules/*/Resources/assets/*/*/sass/main.scss",
    "modules/*/Resources/assets/*/*/js/create.js",
    "modules/*/Resources/assets/*/*/js/edit.js",
    "modules/User/Resources/assets/admin/sass/*",
    "modules/User/Resources/assets/admin/js/*",
];

export default defineConfig(({ command, mode }) => {
    return {
        base: "",
        plugins: [
            laravel({
                input: [
                    "resources/sass/install/main.scss",
                    "resources/js/install/main.js",
                    "modules/Admin/Resources/assets/sass/main.scss",
                    "modules/Admin/Resources/assets/js/main.js",
                    "modules/Admin/Resources/assets/js/app.js",
                    "modules/Admin/Resources/assets/sass/dashboard.scss",
                    "modules/Admin/Resources/assets/js/dashboard.js",
                    "modules/Order/Resources/assets/admin/sass/print.scss",
                    "modules/Storefront/Resources/assets/public/sass/auth.scss",
                    "modules/Storefront/Resources/assets/public/js/auth.js",
                    "modules/Storefront/Resources/assets/public/js/vendors/flatpickr.js",
                    "modules/Storefront/Resources/assets/public/sass/pages/_blog-post.scss",
                    ...glob.sync(`{${ASSET_PATHS.join(",")}}`),
                ],
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
            copy({
                targets: [
                    {
                        src: [
                            "public/favicon.ico",
                            "node_modules/jquery/dist/jquery.min.js",
                            "modules/Admin/node_modules/tinymce",
                            "modules/Admin/node_modules/selectize/dist/js/standalone/selectize.min.js",
                            "modules/Admin/Resources/assets/images/*",
                            "modules/Category/node_modules/jstree/dist/jstree.min.js",
                            "modules/Storefront/node_modules/bootstrap/dist/js/bootstrap.min.js",
                            "modules/Storefront/node_modules/slick-carousel/slick/slick.min.js",
                            "modules/Storefront/Resources/assets/public/images/*",
                        ],
                        dest: "public/build/assets",
                    },
                    {
                        src: "modules/Storefront/node_modules/line-awesome/dist/line-awesome/fonts",
                        dest: ["modules/Storefront/Resources/assets/public"],
                    },
                ],
                copyOnce: true,
                hook: command === "build" ? "writeBundle" : "buildStart",
            }),
        ],
        css: {
            postcss: {
                plugins: [
                    autoprefixer(),
                    postcssRTLCSS({
                        ltrPrefix: ".ltr",
                        rtlPrefix: ".rtl",
                        processKeyFrames: true,
                    }),
                ],
            },
        },
        resolve: {
            alias: {
                vue: path.resolve(
                    __dirname,
                    "./node_modules/vue/dist/vue.esm.js"
                ),
                "@admin": path.resolve(
                    __dirname,
                    "./modules/Admin/Resources/assets"
                ),
                "@modules": path.resolve(__dirname, "./modules"),
            },
        },
        build: {
            sourcemap: mode === "development",
            rollupOptions: {
                output: {
                    manualChunks(id) {
                        if (id.includes("node_modules")) {
                            return id
                                .toString()
                                .split("node_modules/")[1]
                                .split("/")[0]
                                .toString();
                        }
                    },
                    entryFileNames: `assets/[name]-[hash]-v${FLEETCART_VERSION}.js`,
                    chunkFileNames: `assets/[name]-[hash]-v${FLEETCART_VERSION}.js`,
                    assetFileNames: function () {
                        return `assets/[name]-[hash]-v${FLEETCART_VERSION}.[ext]`;
                    },
                },
            },
        },
    };
});
