import { defineConfig } from '@playwright/test';
export default defineConfig({
 testDir: './tests/Browser',
 workers: 1,
 retries: 0,
 reporter: 'list',
 use: { baseURL: process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1:8000', trace: 'off', screenshot: 'only-on-failure', launchOptions: {executablePath: process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE || '/usr/bin/chromium', args: ['--no-sandbox']} },
 projects: [{name:'mobile', use:{viewport:{width:360,height:800}}},{name:'desktop',use:{viewport:{width:1440,height:1000}}}],
});
