import { test, expect } from "@playwright/test";
test("landing, marca, acceso y feedback sin backend simulado", async ({
    page,
    request,
}, info) => {
    const errors = [];
    page.on("pageerror", (e) => errors.push(e.message));
    for (const path of [
        "/",
        "/marca",
        "/login",
        "/register",
        "/feedback",
        "/beta",
    ]) {
        await page.goto(path);
        await expect(page.locator("h1").first()).toBeVisible();
        await page.evaluate(() => document.fonts.ready);
        expect(
            await page.evaluate(
                () => document.documentElement.scrollWidth <= innerWidth,
            ),
        ).toBe(true);
        const sig = page.getByRole("link", {
            name: "DESARROLLADO POR NICOLAS AGUIRRES",
        });
        await expect(sig).toHaveAttribute(
            "href",
            "https://nicolasaguirrescode.com/",
        );
        await expect(sig).toHaveAttribute("rel", "noopener noreferrer");
        for (const img of await page.locator("img").all())
            expect(
                await img.evaluate((el) => el.complete && el.naturalWidth > 0),
            ).toBe(true);
    }
    await page.goto("/");
    await page.screenshot({
        path: info.outputPath("landing.png"),
        fullPage: true,
    });
    expect(errors).toEqual([]);
    expect((await request.get("/up")).status()).toBe(200);
});
test("sin Supabase explica el bloqueo y conserva datos de feedback", async ({
    page,
}) => {
    await page.goto("/feedback");
    await page
        .getByLabel("Descripción")
        .fill("No puedo configurar el primer presupuesto.");
    await page.getByRole("button", { name: "Guardar feedback" }).click();
    await expect(page.locator("p[role=alert]")).toContainText(
        "Falta conectar Supabase",
    );
    await expect(page.getByLabel("Descripción")).toHaveValue(
        "No puedo configurar el primer presupuesto.",
    );
});
