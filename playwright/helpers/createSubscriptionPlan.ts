import { Page } from '@playwright/test';

export async function createSubscriptionPlan(page: Page) {
  await page.getByRole('link', { name: ' Subscriptions ' }).click();
  await page.getByRole('link', { name: '+ Create Plan' }).nth(1).click();
  await page.getByRole('textbox', { name: 'Plan Name *' }).fill('Pro Plan');
  await page.getByRole('textbox', { name: 'Description' }).fill('Plan With Full Modules');
  await page.getByText('Complete order processing and').click();
  await page.getByRole('checkbox', { name: 'Reservation System Table' }).check();
  await page.getByRole('checkbox', { name: 'Inventory Management Stock' }).check();
  await page.getByRole('checkbox', { name: 'Menu Management Menu items,' }).check();
  await page.getByRole('checkbox', { name: 'Customer Management Customer' }).check();
  await page.locator('label').filter({ hasText: 'Kitchen Operations Kitchen' }).click();
  await page.locator('label').filter({ hasText: 'Reports & Analytics Business' }).click();
  await page.locator('label').filter({ hasText: 'System Administration System' }).click();
  await page.getByPlaceholder('0.00').fill('35000.00');
  await page.getByRole('button', { name: ' Create Plan' }).click();
}
