import { test, expect } from '@playwright/test';
import {checkAuthorSignature} from './helpers/author.js';
test('landing, panel, editor y decisión pública funcionan sin desbordes', async ({ page, context }, testInfo) => {
 const errors=[];page.on('pageerror',e=>errors.push(e.message));
 const capture=async name=>{
  await checkAuthorSignature(page,{keyboard:true});
  await expect.poll(()=>page.evaluate(()=>document.documentElement.scrollWidth <= window.innerWidth)).toBe(true);
  await page.screenshot({path:testInfo.outputPath(`${name}.png`),fullPage:true});
 };
 await page.goto('/');await expect(page.getByRole('heading',{name:/Cada trabajo.*en orden/})).toBeVisible();await capture('landing');
 await page.getByRole('button',{name:/Probar demo/}).click();await expect(page).toHaveURL(/\/app$/);await expect(page.getByRole('heading',{name:'Qué requiere atención'})).toBeVisible();await capture('panel');
 await page.getByRole('link',{name:/Nuevo presupuesto/}).click();await page.getByLabel(/^Título/).fill('Biblioteca de prueba');await page.getByLabel(/^Qué incluye el trabajo/).fill('Biblioteca a medida con instalación.');
 await page.getByLabel(/^Descripción/).fill('Fabricación');await page.getByLabel(/^Precio unitario/).fill('1000.50');
 await expect(page.getByRole('button',{name:'Quitar ítem'})).toBeDisabled();await page.getByRole('button',{name:'Agregar ítem'}).click();await page.getByLabel(/^Descripción/).nth(1).fill('Instalación');await page.getByLabel(/^Cantidad/).nth(1).fill('2');await page.getByLabel(/^Precio unitario/).nth(1).fill('250');await page.getByRole('button',{name:'Agregar ítem'}).click();await page.getByRole('button',{name:'Quitar ítem'}).last().click();await expect(page.locator('.quote-item')).toHaveCount(2);await capture('editor');
 await page.getByRole('button',{name:/Guardar y revisar/}).click();await expect(page.getByRole('heading',{name:'Biblioteca de prueba'})).toBeVisible();await expect(page.getByText('ARS 1.500,50').first()).toBeVisible();
 await page.getByRole('button',{name:'Publicar versión',exact:true}).click();const url=await page.getByLabel('Copiá y guardá el enlace').inputValue();
 const internalPrint=await context.newPage();await internalPrint.goto(await page.locator('a[href*="/app/versions/"][href$="/print"]').first().getAttribute('href'));await internalPrint.emulateMedia({media:'print'});await checkAuthorSignature(internalPrint);await internalPrint.pdf({path:testInfo.outputPath('presupuesto-interno.pdf'),format:'A4',printBackground:true});await internalPrint.close();
 const guest=await context.browser().newContext({viewport:page.viewportSize()});const visitor=await guest.newPage();await visitor.goto(url);await expect(visitor.getByRole('heading',{name:'Biblioteca de prueba'})).toBeVisible();await checkAuthorSignature(visitor,{keyboard:true});
 expect(await visitor.evaluate(()=>document.documentElement.scrollWidth <= window.innerWidth)).toBe(true);await visitor.screenshot({path:testInfo.outputPath('presupuesto-publico.png'),fullPage:true});
 await visitor.emulateMedia({media:'print'});await expect(visitor.locator('.decision-box')).toBeHidden();await checkAuthorSignature(visitor);await expect(visitor.locator('.site-footer > span')).toBeHidden();const pdf=await visitor.pdf({path:testInfo.outputPath('presupuesto.pdf'),format:'A4',printBackground:true});expect(pdf.subarray(0,5).toString()).toBe('%PDF-');await visitor.emulateMedia({media:'screen'});
 await visitor.getByLabel(/^Tu nombre/).fill('Cliente de prueba');await visitor.getByLabel(/Confirmo que revisé/).check();await visitor.getByRole('button',{name:'Registrar mi decisión'}).click();await expect(visitor.getByRole('status')).toContainText('Registramos tu decisión');await guest.close();
 await page.reload();await page.getByRole('button',{name:/Abrir \/ crear trabajo/}).click();await expect(page.getByRole('heading',{name:'Biblioteca de prueba'})).toBeVisible();await page.getByLabel('Importe en ARS').fill('500');await page.getByRole('button',{name:'Registrar cobro',exact:true}).click();await expect(page.getByRole('status')).toContainText('Cobro registrado');await expect(page.getByText('ARS 1.000,50').first()).toBeVisible();
 await page.getByRole('link',{name:'Actualizar trabajo'}).click();await page.getByLabel('Estado',{exact:true}).selectOption('terminado');await page.getByRole('button',{name:'Guardar trabajo',exact:true}).click();await expect(page.getByRole('status')).toContainText('Trabajo guardado');
 await page.getByRole('link',{name:'Inicio',exact:true}).click();await expect(page.getByText('Biblioteca de prueba').last()).toBeVisible();
 expect(errors).toEqual([]);
});
