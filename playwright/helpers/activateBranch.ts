import { Page } from '@playwright/test';

export async function activateBranch(page: Page) {
  // Go to branch summary
  await page.goto('https://rms.test/admin/branches/1/summary');

  // Handle dialog and click "Copy"
  page.once('dialog', async dialog => {
    await dialog.dismiss();
  });
  await page.getByRole('button', { name: 'Copy' }).click();

  // Wait for the activation key textbox to be visible and get its value
  const keyTextbox = await page.getByRole('textbox').first();
  await keyTextbox.waitFor({ state: 'visible' });
  const activationKey = (await keyTextbox.inputValue()).trim();

  // Go to activation page and use the key
  await page.goto('https://rms.test/admin/branches/activate');
  await page.getByRole('textbox').fill(activationKey);
  await page.getByRole('button', { name: 'Activate' }).click();
}
