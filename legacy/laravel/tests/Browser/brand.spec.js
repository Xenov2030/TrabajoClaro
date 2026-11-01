import {test,expect} from '@playwright/test';
import {checkAuthorSignature} from './helpers/author.js';
const tagline='Cada trabajo en orden. Cada cobro, claro.';
async function loadedBrand(page){
 await page.evaluate(()=>document.fonts.ready);
 expect(await page.evaluate(()=>document.fonts.check('700 16px Manrope'))).toBe(true);
 for(const img of await page.locator('.brand-logo-image').all()){
  await expect(img).toBeVisible();expect(await img.evaluate(el=>el.complete&&el.naturalWidth>0)).toBe(true);
  const rect=await img.boundingBox();expect(rect.width).toBeGreaterThan(20);expect(rect.height).toBeGreaterThan(10);
 }
 expect(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth)).toBe(true);
}
test('marca, variantes y activos reales cargan sin deformación',async({page,request},testInfo)=>{
 const failed=[];page.on('requestfailed',req=>failed.push(req.url()));
 await page.goto('/marca');await loadedBrand(page);await expect(page.locator('.brand-presentation .brand-tagline')).toHaveCount(2);await expect(page.locator('.brand-presentation .brand-tagline').first()).toHaveText(tagline);
 await checkAuthorSignature(page);await page.screenshot({path:testInfo.outputPath('marca-variantes.png'),fullPage:true});
 const assets=[['/favicon.svg','<svg'],['/favicon.ico',null],['/favicon-16x16.png',null],['/favicon-32x32.png',null],['/apple-touch-icon.png',null],['/icon-192.png',null],['/icon-512.png',null],['/fonts/manrope/Manrope-Variable.ttf',null],['/fonts/manrope/OFL.txt','SIL OPEN FONT LICENSE']];
 for(const [url,content] of assets){const response=await request.get(url);expect(response.status(),url).toBe(200);const body=await response.body();expect(body.length,url).toBeGreaterThan(100);if(content)expect(body.toString()).toContain(content);if(url.endsWith('.png'))expect([...body.subarray(0,8)]).toEqual([137,80,78,71,13,10,26,10]);if(url.endsWith('.ico'))expect([...body.subarray(0,4)]).toEqual([0,0,1,0]);}
 const manifest=await (await request.get('/site.webmanifest')).json();expect(manifest.display).toBe('browser');expect(manifest.name).toBe('TrabajoClaro');expect(manifest.icons.map(icon=>icon.sizes)).toEqual(['192x192','512x512']);
 await page.goto('/');await loadedBrand(page);await expect(page.getByRole('heading',{level:1})).toHaveText(tagline);await expect(page).toHaveTitle(/TrabajoClaro/);await expect(page.locator('link[rel="apple-touch-icon"]')).toHaveAttribute('href','/apple-touch-icon.png');await expect(page.locator('link[rel="manifest"]')).toHaveAttribute('href','/site.webmanifest');await page.screenshot({path:testInfo.outputPath('landing-marca.png'),fullPage:true});
 for(const path of ['/login','/register']){await page.goto(path);await loadedBrand(page);await expect(page.locator('.auth-brand .brand-tagline')).toHaveText(tagline);await checkAuthorSignature(page);await page.screenshot({path:testInfo.outputPath(path.slice(1)+'-marca.png'),fullPage:true});}
 expect(failed).toEqual([]);
});
