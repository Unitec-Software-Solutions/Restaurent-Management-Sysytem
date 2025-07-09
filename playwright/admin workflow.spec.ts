import { test, expect } from '@playwright/test';
import { loginAsSuperAdmin } from './helpers/loginAsSuperAdmin'
import { createSubscriptionPlan } from './helpers/createSubscriptionPlan';
import { createOrganization } from './helpers/createOrganization';
// import { activateBranch } from './helpers/activateBranch';
import { createSupplier } from './helpers/createSupplier';

test('admin workflow', async ({ page }) => {
  await loginAsSuperAdmin(page);
    await createSubscriptionPlan(page);
    await createOrganization(page);
    await createSupplier(page, {
        organization: '1',
        companyName: 'Supplier -001',
        hasVat: true,
        vatNumber: 'Vat-0987654321',
        address: 'Addres-0091',
        contactPerson: 'Contact - person -001',
        phone: '0712345678',
        email: 'sup1231@mail.com'
    });

    await createSupplier(page, {
        organization: '1',
        companyName: 'supplier -002',
        hasVat: true,
        vatNumber: 'vat-0123791280',
        address: 'addres -002',
        contactPerson: 'contact -003',
        phone: '0732165498',
        email: 'Mail-sup-0021@mail.com'
    });

    
});
