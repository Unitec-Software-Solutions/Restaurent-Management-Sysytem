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
    console.log('\n📄 ◾ Navigating to suppliers page');
    await page.goto('https://rms-unitec.test/admin/suppliers');
    const testRunId = process.env.TEST_RUN_ID || 'default';
    await page.screenshot({ path: `test-results/screen-shots/${testRunId}/supplier-step-1-suppliers-list.png` });

    console.log('➕ ◾ Clicking "+ Add New Supplier"');
    // await page.getByRole('link', { name: '+ Add New Supplier' }).click();
    await page.goto('https://rms-unitec.test/admin/suppliers/create');
    await page.screenshot({ path: `test-results/screen-shots/${testRunId}/supplier-step-2-add-form.png` });

    console.log('🏢 ◾ Selecting organization:', supplier.organization);
    await page.getByLabel('Organization *').selectOption(supplier.organization);

    console.log('🏢 Filling company name:', supplier.companyName);
    await page.getByRole('textbox', { name: 'Company Name *' }).fill(supplier.companyName);

    if (supplier.hasVat) {
        console.log('✅ Checking VAT registration');
        await page.getByRole('checkbox', { name: 'Has VAT Registration' }).check();
        if (supplier.vatNumber) {
            console.log('💳 Filling VAT number:', supplier.vatNumber);
            await page.getByRole('textbox', { name: 'VAT Registration Number' }).fill(supplier.vatNumber);
        }
    }

    if (supplier.address) {
        console.log('🏠 Filling address:', supplier.address);
        await page.getByRole('textbox', { name: 'Company Address' }).fill(supplier.address);
    }

    await page.screenshot({ path: `test-results/screen-shots/${testRunId}/supplier-step-3-company-details.png` });

    console.log('➡️ ◾ Proceeding to contact details');
    await page.getByRole('button', { name: 'Next: Contact Details ' }).click();

    console.log('👤 📇 Filling contact person:', supplier.contactPerson);
    await page.getByRole('textbox', { name: 'Contact Person *' }).fill(supplier.contactPerson);
    console.log('📞 Filling phone:', supplier.phone);
    await page.getByRole('textbox', { name: 'Phone *' }).fill(supplier.phone);
    console.log('📧 📩 Filling email:', supplier.email);
    await page.getByRole('textbox', { name: 'Email' }).fill(supplier.email);

    await page.screenshot({ path: `test-results/screen-shots/${testRunId}/supplier-step-4-contact-details.png` });

    console.log('🚀 ✅ Submitting supplier form');
    await page.getByRole('button', { name: 'Create Supplier' }).click();

    console.log('🔙 Navigating back to suppliers management page');
    await page.goto('https://rms-unitec.test/admin/suppliers');
    await page.screenshot({ path: `test-results/screen-shots/${testRunId}/supplier-step-1-suppliers-list.png` });

    console.log(`\n✨ Supplier ${supplier.companyName} creation completed`);
}
