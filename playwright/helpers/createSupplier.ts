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
  await page.goto('https://restaurent-management-sysytem.test/admin/suppliers');
  await page.getByRole('link', { name: '+ Add New Supplier' }).click();
  await page.getByLabel('Organization *').selectOption(supplier.organization);
  await page.getByRole('textbox', { name: 'Company Name *' }).fill(supplier.companyName);
  if (supplier.hasVat) {
    await page.getByRole('checkbox', { name: 'Has VAT Registration' }).check();
    if (supplier.vatNumber) {
      await page.getByRole('textbox', { name: 'VAT Registration Number' }).fill(supplier.vatNumber);
    }
  }
  if (supplier.address) {
    await page.getByRole('textbox', { name: 'Company Address' }).fill(supplier.address);
  }
  await page.getByRole('button', { name: 'Next: Contact Details ' }).click();
  await page.getByRole('textbox', { name: 'Contact Person *' }).fill(supplier.contactPerson);
  await page.getByRole('textbox', { name: 'Phone *' }).fill(supplier.phone);
  await page.getByRole('textbox', { name: 'Email' }).fill(supplier.email);
  await page.getByRole('button', { name: 'Create Supplier' }).click();
}
