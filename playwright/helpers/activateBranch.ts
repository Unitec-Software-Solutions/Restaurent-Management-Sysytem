import { Page } from '@playwright/test';

export async function activateBranch(page: Page) {
  await page.getByRole('link', { name: ' Branches ' }).click();
  await page.getByRole('link', { name: 'View' }).click();
  page.once('dialog', dialog => dialog.dismiss().catch(() => {}));
  await page.getByRole('button', { name: 'Copy' }).click();
  await page.getByRole('link', { name: ' Branches ' }).click();
  await page.getByRole('link', { name: ' Activate Branch' }).click();
  await page.getByRole('textbox').click();
  await page.keyboard.press('Control+V');
  await page.getByRole('button', { name: 'Activate' }).click();
}
