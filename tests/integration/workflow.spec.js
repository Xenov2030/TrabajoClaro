import { test, expect } from "@playwright/test";
import { session } from "./session.mjs";
test("recorrido real PostgreSQL desde sesión de prueba, sin afirmar Auth alojado", async ({
    page,
    browser,
}, info) => {
    await page.addInitScript(
        (s) => localStorage.setItem("sb-127-auth-token", JSON.stringify(s)),
        session(),
    );
    await page.goto("/auth/callback");
    await expect(page).toHaveURL(/\/app$/);
    await expect(
        page.getByRole("heading", { name: "Qué requiere atención" }),
    ).toBeVisible();
    await page.screenshot({
        path: info.outputPath("panel.png"),
        fullPage: true,
    });
    await page.getByRole("link", { name: "Clientes", exact: true }).click();
    await page
        .getByRole("link", { name: "Nuevo cliente", exact: true })
        .click();
    await page
        .getByLabel("Nombre *", { exact: true })
        .fill("Cliente " + info.project.name);
    await page.getByRole("button", { name: "Guardar", exact: true }).click();
    await expect(
        page.getByRole("heading", { name: "Editar cliente" }),
    ).toBeVisible();
    await page.getByRole("link", { name: "Presupuestos", exact: true }).click();
    await page
        .getByRole("link", { name: "Nuevo presupuesto", exact: true })
        .click();
    await page
        .getByLabel("Título *", { exact: true })
        .fill("Estante " + info.project.name);
    await page
        .getByLabel("Qué incluye el trabajo")
        .fill("Fabricación e instalación");
    await page.getByLabel("Descripción *", { exact: true }).fill("Fabricación");
    await page.getByLabel("Precio unitario").fill("1000.50");
    await page
        .getByRole("button", { name: "Agregar ítem", exact: true })
        .click();
    await page
        .getByLabel("Descripción *", { exact: true })
        .nth(1)
        .fill("Instalación");
    await page.getByLabel("Cantidad *", { exact: true }).nth(1).fill("2");
    await page.getByLabel("Precio unitario").nth(1).fill("250");
    await page.screenshot({
        path: info.outputPath("editor.png"),
        fullPage: true,
    });
    await page
        .getByRole("button", { name: "Guardar y revisar totales" })
        .click();
    await expect(
        page.getByRole("heading", { name: "Total: ARS 1.500,50" }),
    ).toBeVisible();
    await page
        .getByRole("button", { name: "Publicar versión", exact: true })
        .click();
    const url = await page.getByLabel("Copiá y guardá el enlace").inputValue();
    expect(url).toContain("/p/#");
    const guest = await browser.newContext({ viewport: page.viewportSize() });
    const client = await guest.newPage();
    await client.goto(url);
    await expect(
        client.getByRole("heading", { name: "Total: ARS 1.500,50" }),
    ).toBeVisible();
    await client.getByLabel("Tu nombre").fill("Cliente ficticio");
    await client.getByLabel("Confirmo que revisé").check();
    await client.getByRole("button", { name: "Registrar mi decisión" }).click();
    await expect(
        client.getByText("Registramos tu decisión para esta versión."),
    ).toBeVisible();
    await client.screenshot({
        path: info.outputPath("publico.png"),
        fullPage: true,
    });
    await client.emulateMedia({ media: "print" });
    await client.pdf({
        path: info.outputPath("presupuesto.pdf"),
        format: "A4",
        printBackground: true,
    });
    await guest.close();
    await page.reload();
    await page.getByRole("button", { name: "Abrir / crear trabajo" }).click();
    await expect(
        page.getByRole("heading", { name: "Saldo: ARS 1.500,50" }),
    ).toBeVisible();
    await page.getByLabel("Importe en ARS").fill("500");
    await page
        .getByRole("button", { name: "Registrar cobro", exact: true })
        .click();
    await expect(
        page.getByRole("heading", { name: "Saldo: ARS 1.000,50" }),
    ).toBeVisible();
    expect(
        await page.evaluate(
            () => document.documentElement.scrollWidth <= innerWidth,
        ),
    ).toBe(true);
    await page.screenshot({
        path: info.outputPath("trabajo.png"),
        fullPage: true,
    });
    await page
        .getByRole("link", { name: "Enviar feedback", exact: true })
        .first()
        .click();
    await page
        .getByLabel("Descripción")
        .fill("Probé el recorrido del presupuesto y el cobro parcial.");
    await page.getByRole("button", { name: "Guardar feedback" }).click();
    await expect(page.getByRole("status")).toContainText(
        "Guardamos tu feedback",
    );
    await page.goto("/");
    await page.getByLabel("Tu nombre").fill("Tester ficticio");
    await page.getByLabel("Tu correo").fill("tester@example.test");
    await page
        .getByLabel("Qué querés probar")
        .fill("Quiero probar el recorrido de un encargo ficticio.");
    await page.getByRole("button", { name: "Guardar solicitud" }).click();
    await expect(
        page.getByText(
            "Guardamos tu solicitud de piloto. No se envían correos automáticos.",
        ),
    ).toBeVisible();
});
