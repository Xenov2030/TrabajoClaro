// Rasterizar el SVG original con Chromium; no cambia el formato por renombrado.
import { chromium } from "@playwright/test";
import { readFile, writeFile, mkdir } from "node:fs/promises";
import { fileURLToPath } from "node:url";
import path from "node:path";
const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const source = await readFile(path.join(root, "public/favicon.svg"), "utf8");
const browser = await chromium.launch({
    executablePath:
        process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE || "/usr/bin/chromium",
    args: ["--no-sandbox"],
});
try {
    for (const size of [192, 512]) {
        const page = await browser.newPage({
            viewport: { width: size, height: size },
            deviceScaleFactor: 1,
        });
        const inner = source
            .replace(/<svg[^>]*>/, "")
            .replace(/<\/svg>\s*$/, "");
        await page.setContent(
            `<style>html,body{margin:0;width:100%;height:100%;background:#16634f}svg{display:block;width:100%;height:100%}</style><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" fill="#16634f"/><g transform="translate(20 20) scale(.9375)">${inner}</g></svg>`,
        );
        await page.screenshot({
            path: path.join(root, "public", `icon-${size}-maskable.png`),
        });
        await page.close();
    }
    for (const [size, name] of [
        [16, "favicon-16x16.png"],
        [32, "favicon-32x32.png"],
        [180, "apple-touch-icon.png"],
        [192, "icon-192.png"],
        [512, "icon-512.png"],
    ]) {
        const page = await browser.newPage({
            viewport: { width: size, height: size },
            deviceScaleFactor: 1,
        });
        await page.setContent(
            `<style>html,body{margin:0;padding:0;width:${size}px;height:${size}px}svg{display:block;width:100%;height:100%}</style>${source}`,
        );
        await page.screenshot({
            path: path.join(root, "public", name),
            omitBackground: true,
        });
        await page.close();
    }
} finally {
    await browser.close();
}
