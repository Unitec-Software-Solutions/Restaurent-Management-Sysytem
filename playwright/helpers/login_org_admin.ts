import { Page } from '@playwright/test';

export async function loginAsOrganizationAdmin(page: Page) {
  await page.goto('https://restaurant-management-system.test/admin/login');
  await page.getByRole('textbox', { name: 'Email' }).fill('rgadmin@rms.com');
  await page.getByRole('textbox', { name: 'Password' }).fill('OrgAdmin123!');
  await page.getByRole('button', { name: 'Login' }).click();
}
