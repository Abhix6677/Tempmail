import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import fs from "fs";
import { homedir } from "os";
import { resolve } from "path";

let host = "tmail8.test";

function detectServerConfig(host) {
    let keyPath = resolve(homedir(), `.config/valet/Certificates/${host}.key`);
    let certificatePath = resolve(homedir(), `.config/valet/Certificates/${host}.crt`);
    if (!fs.existsSync(keyPath)) {
        return {};
    }
    if (!fs.existsSync(certificatePath)) {
        return {};
    }
    return {
        hmr: { host },
        host,
        https: {
            key: fs.readFileSync(keyPath),
            cert: fs.readFileSync(certificatePath),
        },
    };
}

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/sass/common.scss", "resources/js/app.js"],
            refresh: true,
        }),
    ],
    server: detectServerConfig(host),
});
