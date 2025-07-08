// playwright/tests/login.spec.ts
import { test as base, expect } from '@playwright/test';

// Only use Chrome (Chromium)
const test = base.extend({
  browserName: 'chromium',
});

// Admin Login Test

test.describe('Admin Login', () => {
  test('should show admin login page and allow admin login', async ({ page }) => {
    await page.goto('http://restaurent-management-sysytem.test/admin/login');
    await expect(page).toHaveURL(/\/admin\/login$/);
    await expect(page.locator('h1, h1.text-3xl')).toContainText(/admin login/i);

    // Fill in credentials (replace with valid admin user)
    await page.fill('input[name="email"]', 'superadmin@rms.com');
    await page.fill('input[name="password"]', 'SuperAdmin123!');
    await page.click('button[type="submit"]');

    // Should redirect to admin dashboard
    await expect(page).not.toHaveURL(/\/admin\/login$/);
    // Optionally check for dashboard element
    // await expect(page.locator('text=Admin Dashboard')).toBeVisible();
  });
});
