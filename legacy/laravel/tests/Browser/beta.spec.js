import {test,expect} from '@playwright/test';
import {checkAuthorSignature} from './helpers/author.js';
test('feedback guarda pantalla y muestra carga y confirmación',async({page},testInfo)=>{
 await page.goto('/');await page.locator('.beta-banner .feedback-link').click();await expect(page.getByLabel('Pantalla',{exact:true})).toHaveValue('landing');await page.getByLabel(/^¿Qué pasó/).fill('Quiero poder entender mejor las fechas de entrega.');
 // Retener la navegación nativa para revisar el estado transitorio antes de continuar el POST real.
 await page.evaluate(()=>document.addEventListener('submit',event=>event.preventDefault(),{once:true,capture:true}));
 await page.getByRole('button',{name:'Enviar feedback',exact:true}).click();
 await expect(page.getByRole('button',{name:'Guardando feedback…'})).toBeDisabled();await expect(page.locator('form[aria-busy="true"]')).toBeVisible();await page.screenshot({path:testInfo.outputPath('feedback-cargando.png'),fullPage:true});
 await page.locator('form[aria-busy="true"]').evaluate(form=>HTMLFormElement.prototype.submit.call(form));
 await expect(page.getByRole('status')).toContainText('Guardamos tu feedback');
 expect(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth)).toBe(true);await page.screenshot({path:testInfo.outputPath('feedback-confirmado.png'),fullPage:true});
});
test('cada tester registra su negocio y vuelve a ingresar',async({page},testInfo)=>{
 const email=`tester-${testInfo.project.name}-${Date.now()}@example.test`,password='PruebaPrivada2026!';await page.goto('/register');await checkAuthorSignature(page,{keyboard:true});await page.getByLabel('Tu nombre',{exact:false}).fill('Tester independiente');await page.getByLabel(/^Correo/).fill(email);await page.getByLabel('Contraseña',{exact:false}).first().fill(password);await page.getByLabel(/^Repetí/).fill(password);await page.getByLabel(/^Nombre del negocio/).fill('Instalaciones de ejemplo para probar');await page.getByLabel(/^Rubro/).fill('Instalaciones');await page.getByRole('button',{name:'Crear cuenta de prueba',exact:true}).click();await expect(page).toHaveURL(/\/app$/);await expect(page.getByRole('heading',{name:'Qué requiere atención'})).toBeVisible();await page.getByRole('button',{name:'Salir',exact:true}).click();await page.goto('/login');await checkAuthorSignature(page,{keyboard:true});await page.getByLabel(/^Correo/).fill(email);await page.getByLabel(/^Contraseña/).fill(password);await page.getByRole('button',{name:'Ingresar',exact:true}).click();await expect(page).toHaveURL(/\/app$/);await expect(page.getByRole('heading',{name:'Qué requiere atención'})).toBeVisible();expect(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth)).toBe(true);
});
