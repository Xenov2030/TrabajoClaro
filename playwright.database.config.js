import { defineConfig } from "@playwright/test";
export default defineConfig({
    testDir: "./tests/integration",
    outputDir: "test-results/integration",
    testMatch: "*.spec.js",
    workers: 1,
    retries: 0,
    reporter: "list",
    use: {
        baseURL: "http://127.0.0.1:3001",
        launchOptions: process.env.PLAYWRIGHT_BUNDLED
            ? {}
            : { executablePath: "/usr/bin/chromium", args: ["--no-sandbox"] },
    },
    webServer: {
        command: "node scripts/serve-database-test.mjs",
        url: "http://127.0.0.1:3001/up",
        timeout: 60000,
    },
    projects: [
        { name: "mobile", use: { viewport: { width: 360, height: 800 } } },
        { name: "desktop", use: { viewport: { width: 1440, height: 1000 } } },
    ],
});
