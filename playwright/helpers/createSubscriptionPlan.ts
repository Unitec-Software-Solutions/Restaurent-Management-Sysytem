import { Page } from '@playwright/test';

export async function createSubscriptionPlan(page: Page) {
    // Navigate to Subscriptions and Create Plan
    await page.goto(`https://rms-unitec.test/admin/subscription-plans/create`);

  // Fill basic information
  await page.getByLabel('Plan Name').fill('Pro Plan');
  await page.getByLabel('Currency').selectOption('LKR');
  await page.getByLabel('Description').fill('Plan With Full Modules');

  // Select modules by label text
  await page.getByLabel('Menu Management').check();
  await page.getByLabel('Order Management').check();
  await page.getByLabel('Inventory Management').check();
  await page.getByLabel('Reservation Management').check();
  await page.getByLabel('Staff Management').check();
  await page.getByLabel('Reporting').check();

  await page.getByLabel('System Settings').check();

  // Fill pricing and limits
  await page.locator('input[name="price"]').fill('35000.00');
  await page.locator('input[name="max_branches"]').fill('10'); // Example value
  await page.locator('input[name="max_employees"]').fill('100'); // Example value

  // Enable trial and set trial days
  await page.locator('input[name="is_trial"]').check();
  await page.locator('input[name="trial_period_days"]').fill('30');

  // Set plan as active
  await page.locator('input[name="is_active"]').check();

  // Submit the form
  await page.locator('button[type="submit"]:has-text("Create Plan")').click();
}
