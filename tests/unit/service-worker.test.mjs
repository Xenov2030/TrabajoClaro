import { test } from "node:test";
import assert from "node:assert/strict";
import { readFile } from "node:fs/promises";
import { runInNewContext } from "node:vm";
const source = await readFile(
    new URL("../../public/sw.js", import.meta.url),
    "utf8",
);
function worker() {
    const handlers = {};
    const calls = [];
    const self = {
        location: { origin: "https://trabajoclaro.example" },
        addEventListener: (name, fn) => (handlers[name] = fn),
        skipWaiting: () =>
            assert.fail(
                "No debe activar una actualización sin cuidar formularios",
            ),
        get clients() {
            assert.fail("No debe reemplazar ni recargar ventanas");
        },
    };
    runInNewContext(source, {
        self,
        URL,
        fetch: (r) => {
            calls.push(r.url);
            return Promise.resolve({ ok: true });
        },
        get caches() {
            assert.fail("No debe almacenar datos en CacheStorage");
        },
    });
    return { handlers, calls };
}
test("no intercepta navegación, autenticación, datos ni escrituras", () => {
    const w = worker();
    for (const [path, method, mode] of [
        ["/app/quotes/123", "GET", "navigate"],
        ["/p/", "GET", "navigate"],
        ["/feedback", "POST", "cors"],
        ["/app", "GET", "cors"],
        ["https://project.supabase.co/auth/v1/token", "POST", "cors"],
        ["https://project.supabase.co/rest/v1/tc_records", "GET", "cors"],
    ]) {
        w.handlers.fetch({
            request: {
                url: path.startsWith("https:")
                    ? path
                    : "https://trabajoclaro.example" + path,
                method,
                mode,
            },
            respondWith: () => assert.fail("Interceptó una operación privada"),
        });
    }
    assert.equal(w.calls.length, 0);
});
test("solo reenvía recursos públicos a la red y no fuerza actualizaciones", () => {
    const w = worker();
    w.handlers.install();
    let handled = 0;
    for (const path of [
        "/_next/static/app-hash.js",
        "/brand/logo-horizontal.svg",
        "/fonts/manrope/Manrope-Variable.ttf",
        "/icon-512-maskable.png",
        "/site.webmanifest",
    ]) {
        w.handlers.fetch({
            request: {
                url: "https://trabajoclaro.example" + path,
                method: "GET",
                mode: "cors",
            },
            respondWith: () => handled++,
        });
    }
    assert.equal(handled, 5);
    assert.equal(w.calls.length, 5);
    assert.equal(w.handlers.sync, undefined);
});
