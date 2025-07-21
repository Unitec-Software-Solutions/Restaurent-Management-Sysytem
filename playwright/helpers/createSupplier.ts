import { Page } from '@playwright/test';

export async function createSupplier(page: Page, supplier: {
  organization: string,
  companyName: string,
  hasVat: boolean,
  vatNumber?: string,
  address?: string,
  contactPerson: string,
  phone: string,
  email: string
}) {
  console.log('Navigating to suppliers page');
  await page.goto('https://restaurant-management-system.test/admin/suppliers');
  await page.screenshot({ path: 'supplier-step-1-suppliers-list.png' });

  console.log('Clicking "+ Add New Supplier"');
  await page.getByRole('link', { name: '+ Add New Supplier' }).click();
  await page.goto('https://restaurant-management-system.test/admin/suppliers/create');
  await page.screenshot({ path: 'supplier-step-2-add-form.png' });

  console.log('Selecting organization:', supplier.organization);
  await page.getByLabel('Organization *').selectOption(supplier.organization);

  console.log('Filling company name:', supplier.companyName);
  await page.getByRole('textbox', { name: 'Company Name *' }).fill(supplier.companyName);

  if (supplier.hasVat) {
    console.log('Checking VAT registration');
    await page.getByRole('checkbox', { name: 'Has VAT Registration' }).check();
    if (supplier.vatNumber) {
      console.log('Filling VAT number:', supplier.vatNumber);
      await page.getByRole('textbox', { name: 'VAT Registration Number' }).fill(supplier.vatNumber);
    }
  }

  if (supplier.address) {
    console.log('Filling address:', supplier.address);
    await page.getByRole('textbox', { name: 'Company Address' }).fill(supplier.address);
  }

  await page.screenshot({ path: 'supplier-step-3-company-details.png' });

  console.log('Proceeding to contact details');
  await page.getByRole('button', { name: 'Next: Contact Details ' }).click();

  console.log('Filling contact person:', supplier.contactPerson);
  await page.getByRole('textbox', { name: 'Contact Person *' }).fill(supplier.contactPerson);
  console.log('Filling phone:', supplier.phone);
  await page.getByRole('textbox', { name: 'Phone *' }).fill(supplier.phone);
  console.log('Filling email:', supplier.email);
  await page.getByRole('textbox', { name: 'Email' }).fill(supplier.email);

  await page.screenshot({ path: 'supplier-step-4-contact-details.png' });

  console.log('Submitting supplier form');
  await page.getByRole('button', { name: 'Create Supplier' }).click();

  // Wait for navigation or success message
  try {
    await page.waitForURL(/\/admin\/suppliers(\?.*)?$/, { timeout: 10000 });
    console.log('Supplier created and redirected to suppliers list');
    await page.screenshot({ path: 'supplier-step-5-success.png' });
  } catch (e) {
    console.warn('Did not redirect after create, checking for errors or success message');
    await page.screenshot({ path: 'supplier-step-5-error-or-still-on-form.png' });
  }
}
