import { test, expect } from "@playwright/test";

test("manifest, iconos, instrucciones y worker sin caché privada", async ({
    page,
    request,
}, info) => {
    await page.goto("/instalar");
    await expect(
        page.getByRole("heading", { name: "TrabajoClaro, a mano" }),
    ).toBeVisible();
    await expect(
        page.getByText("Requiere conexión para trabajar.", { exact: false }),
    ).toBeVisible();
    const manifest = await (await request.get("/site.webmanifest")).json();
    expect(manifest.id).toBe("/");
    expect(manifest.start_url).toBe("/");
    expect(manifest.scope).toBe("/");
    expect(manifest.display).toBe("standalone");
    expect(
        manifest.icons
            .filter((i) => i.purpose === "maskable")
            .map((i) => i.sizes),
    ).toEqual(["192x192", "512x512"]);
    for (const icon of manifest.icons) {
        const response = await request.get(icon.src);
        expect(response.status()).toBe(200);
        const bytes = await response.body();
        expect([...bytes.subarray(0, 8)]).toEqual([
            137, 80, 78, 71, 13, 10, 26, 10,
        ]);
        expect(bytes.readUInt32BE(16) + "x" + bytes.readUInt32BE(20)).toBe(
            icon.sizes,
        );
    }
    const reg = await page.evaluate(async () => {
        const r = await navigator.serviceWorker.ready;
        return {
            scope: new URL(r.scope).pathname,
            script: new URL(r.active.scriptURL).pathname,
        };
    });
    expect(reg).toEqual({ scope: "/", script: "/sw.js" });
    await page.reload();
    await expect(
        page.getByRole("heading", { name: "TrabajoClaro, a mano" }),
    ).toBeVisible();
    expect(
        await page.evaluate(() => !!navigator.serviceWorker.controller),
    ).toBe(true);
    const worker = await request.get("/sw.js");
    expect(worker.status()).toBe(200);
    expect(worker.headers()["cache-control"]).toContain("no-store");
    const source = await worker.text();
    expect(source.replace(/\/\*[\s\S]*?\*\/|\/\/[^\n]*/g, "")).not.toMatch(
        /caches\.(open|match)|skipWaiting\s*\(|clients\.claim\s*\(/,
    );
    // The active worker must not intercept navigation/authenticated operations or create caches.
    await page.goto("/feedback");
    await page
        .getByLabel("Descripción")
        .fill("Formulario ficticio conservado ante actualización.");
    expect(await page.evaluate(() => caches.keys())).toEqual([]);
    const value = await page.getByLabel("Descripción").inputValue();
    await page.evaluate(async () => {
        const r = await navigator.serviceWorker.getRegistration();
        await r.update();
    });
    await expect(page.getByLabel("Descripción")).toHaveValue(value);
    expect(
        await page.evaluate(
            () => document.documentElement.scrollWidth <= innerWidth,
        ),
    ).toBe(true);
    await page.goto("/instalar");
    await page.screenshot({
        path: info.outputPath("instalar.png"),
        fullPage: true,
    });
});

test("el diálogo se solicita solo al pulsar y no insiste al cancelar (evento de prueba)", async ({
    page,
}) => {
    await page.goto("/instalar");
    await expect(
        page.getByRole("heading", { name: "TrabajoClaro, a mano" }),
    ).toBeVisible();
    await page.evaluate(() => {
        window.promptCalls = 0;
        const e = new Event("beforeinstallprompt", { cancelable: true });
        e.prompt = async () => {
            window.promptCalls++;
        };
        e.userChoice = Promise.resolve({ outcome: "dismissed" });
        window.dispatchEvent(e);
    });
    const button = page.getByRole("button", {
        name: "Instalar TrabajoClaro",
        exact: true,
    });
    await expect(button).toBeVisible();
    expect(await page.evaluate(() => window.promptCalls)).toBe(0);
    await button.click();
    await expect(page.getByRole("status")).toContainText(
        "Instalación cancelada",
    );
    expect(await page.evaluate(() => window.promptCalls)).toBe(1);
    await expect(button).toHaveCount(0);
    await expect(page.getByText("Este navegador todavía no ofrece un diálogo",{exact:false})).toBeVisible();
});

test("instrucciones iOS y estado standalone sin prometer instalación nativa (emulación)", async ({
    page,
    context,
}) => {
    await context.addInitScript(() => {
        Object.defineProperty(navigator, "platform", { get: () => "MacIntel" });
        Object.defineProperty(navigator, "maxTouchPoints", { get: () => 5 });
    });
    await page.goto("/instalar");
    await expect(
        page.getByRole("heading", { name: "En este iPhone o iPad" }),
    ).toBeVisible();
    await expect(
        page.getByText("En Safari, abrí el menú Compartir", { exact: false }),
    ).toBeVisible();
    await page.evaluate(() => window.dispatchEvent(new Event("appinstalled")));
    await expect(
        page.getByText("Ya estás usando TrabajoClaro en modo aplicación.", {
            exact: true,
        }),
    ).toBeVisible();
    await expect(
        page.getByRole("button", { name: "Instalar TrabajoClaro" }),
    ).toHaveCount(0);
});
