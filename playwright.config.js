import { defineConfig } from "@playwright/test";
export default defineConfig({
    testDir: "./tests/browser",
    outputDir: "test-results/public",
    workers: 1,
    retries: 0,
    reporter: "list",
    use: {
        baseURL: "http://127.0.0.1:3000",
        launchOptions: process.env.PLAYWRIGHT_BUNDLED
            ? {}
            : { executablePath: "/usr/bin/chromium", args: ["--no-sandbox"] },
    },
    webServer: {
        command: "npm run start -- --port 3000",
        url: "http://127.0.0.1:3000/up",
        reuseExistingServer: !process.env.CI,
    },
    projects: [
        { name: "mobile", use: { viewport: { width: 360, height: 800 } } },
        { name: "desktop", use: { viewport: { width: 1440, height: 1000 } } },
    ],
});
