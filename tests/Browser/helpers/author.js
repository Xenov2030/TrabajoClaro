import { expect } from '@playwright/test';
export const signatureText = 'DESARROLLADO POR NICOLAS AGUIRRES';
export const portfolioUrl = 'https://nicolasaguirrescode.com/';
export async function checkAuthorSignature(page, { keyboard = false } = {}) {
 const signature = page.getByRole('link', { name: signatureText, exact: true });
 await expect(signature).toHaveCount(1);
 await signature.scrollIntoViewIfNeeded();
 await expect(signature).toBeVisible();
 await expect(signature).toHaveAttribute('href', portfolioUrl);
 await expect(signature).toHaveAttribute('target', '_blank');
 await expect(signature).toHaveAttribute('rel', 'noopener noreferrer');
 expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
 const box = await signature.boundingBox();
 expect(box.x).toBeGreaterThanOrEqual(0);
 expect(box.x + box.width).toBeLessThanOrEqual(page.viewportSize().width);
 if (keyboard) {
  await page.keyboard.press('Tab');
  await signature.focus();
  await expect(signature).toBeFocused();
  expect(await signature.evaluate(el => { const css = getComputedStyle(el); return css.outlineStyle !== 'none' && parseFloat(css.outlineWidth) >= 2; })).toBe(true);
 }
 return signature;
}
