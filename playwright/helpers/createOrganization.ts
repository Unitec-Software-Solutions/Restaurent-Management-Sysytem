import { Page } from '@playwright/test';

export async function createOrganization(page: Page) {
    await page.getByRole('link', { name: ' Organizations ' }).click();
    await page.getByText('+ Add Organization').click();
    await page.locator('input[name="name"]').fill('Delicious Bites Restaurant');
    await page.getByRole('textbox', { name: 'example@email.com' }).fill('admin@deliciousbites.com');
    await page.locator('input[name="phone"]').fill('0712345678');
        await page.locator('#password').fill('Password@123');
    await page.locator('#password_confirmation').fill('Password@123');
    await page.getByRole('textbox', { name: 'Line 1 Line 2 Line 3 Line' }).fill('123 Main Street, Colombo 03, Sri Lanka');
    await page.locator('input[name="contact_person"]').fill('John de Manager');
    await page.locator('input[name="contact_person_designation"]').fill('General Manager');
    await page.locator('input[name="contact_person_phone"]').fill('0712345678');
    await page.getByText('Status *').click();
    await page.locator('select[name="is_active"]').selectOption('1'); // Active
    await page.getByRole('button', { name: 'Create Organization' }).click();
}
