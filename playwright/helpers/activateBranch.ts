import { Page } from '@playwright/test';

export async function activateBranch(page: Page) {
  // Login as super admin
  await page.goto('https://restaurent-management-sysytem.test/admin/login');
  await page.getByRole('textbox', { name: 'Email' }).fill('superadmin@rms.com');
  await page.getByRole('textbox', { name: 'Password' }).fill('SuperAdmin123!');
  await page.getByRole('button', { name: 'Login' }).click();

  // Go to branch summary
  await page.goto('https://restaurent-management-sysytem.test/admin/branches/1/summary');

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
  await page.goto('https://restaurent-management-sysytem.test/admin/branches/activate');
  await page.getByRole('textbox').fill(activationKey);
  await page.getByRole('button', { name: 'Activate' }).click();
}
