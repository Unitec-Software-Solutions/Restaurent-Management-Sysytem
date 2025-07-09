import { Page } from '@playwright/test';

export async function loginAsSuperAdmin(page: Page) {
  await page.goto('https://restaurent-management-sysytem.test/admin/login');
  await page.getByRole('textbox', { name: 'Email' }).fill('superadmin@rms.com');
  await page.getByRole('textbox', { name: 'Password' }).fill('SuperAdmin123!');
  await page.getByRole('button', { name: 'Login' }).click();
}
