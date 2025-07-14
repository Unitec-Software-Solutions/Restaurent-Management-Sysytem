import { Page } from '@playwright/test';

export async function createOrganization(page: Page) {
    await page.getByRole('link', { name: ' Organizations ' }).click();
    await page.getByText('+ Add Organization').click();
    await page.locator('input[name="name"]').fill('Test-Organization');
    await page.getByRole('textbox', { name: 'example@email.com' }).fill('torg@mail.com');
    await page.locator('input[name="phone"]').fill('0712345678');
    await page.locator('#password').fill('Password@123');
    await page.locator('#password_confirmation').fill('Password@123');
    await page.getByRole('textbox', { name: 'Line 1 Line 2 Line 3 Line' }).fill('addres-090');
    await page.locator('input[name="contact_person"]').fill('Ruwan Kumara');
    await page.locator('input[name="contact_person_designation"]').fill('Owner');
    await page.locator('input[name="contact_person_phone"]').fill('0712345678');
    await page.getByText('Status *').click();
    await page.locator('select[name="is_active"]').selectOption('1');
  await page.getByRole('button', { name: 'Create Organization' }).click();
}
