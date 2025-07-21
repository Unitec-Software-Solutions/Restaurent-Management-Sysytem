import { test, expect } from '@playwright/test';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { loginAsSuperAdmin } from './helpers/loginAsSuperAdmin'
import { createSubscriptionPlan } from './helpers/createSubscriptionPlan';
import { createOrganization } from './helpers/createOrganization';
import { activateBranch } from './helpers/activateBranch';
import { createSupplier } from './helpers/createSupplier';
import { addItemsToInventory } from './helpers/addItemsToInventory';

test.skip('Admin Workflow 🚀', async ({ page }) => {
    // await loginAsSuperAdmin(page);
    // await createSubscriptionPlan(page);
    // await createOrganization(page);
});

test('Create Organization 🏢', async ({ page }) => {
    await loginAsSuperAdmin(page);
    await createOrganization(page);
});

test('Activate Branch 🔑', async ({ page }) => {
        await activateBranch(page);
});

test('Create Supplier 🚚', async ({ page }) => {
    await loginAsSuperAdmin(page);
    await createSupplier(page, {
        organization: '1',
        companyName: 'Keells Super',
        hasVat: true,
        vatNumber: 'VAT-1234567890',
        address: 'Keells Super, Colombo 03, Sri Lanka',
        contactPerson: 'Kasun Perera',
        phone: '0771234567',
        email: 'keells@super.lk'
    });
    await createSupplier(page, {
        organization: '1',
        companyName: 'Arpico Supercentre',
        hasVat: true,
        vatNumber: 'VAT-2345678901',
        address: 'Arpico Supercentre, Hyde Park, Colombo, Sri Lanka',
        contactPerson: 'Nimal Fernando',
        phone: '0719876543',
        email: 'arpico@supercentre.lk'
    });
    await createSupplier(page, {
        organization: '1',
        companyName: 'Laugfs Super',
        hasVat: true,
        vatNumber: 'VAT-3456789012',
        address: 'Laugfs Super, Rajagiriya, Sri Lanka',
        contactPerson: 'Dilani Jayasinghe',
        phone: '0723456789',
        email: 'laugfs@super.lk'
    });
});

test('Add Items to Inventory 📦', async ({ page }) => {
    const __filename = fileURLToPath(import.meta.url);
    const __dirname = path.dirname(__filename);
    const itemsPath = path.resolve(__dirname, 'data/inventory-items.json');
    const items = JSON.parse(fs.readFileSync(itemsPath, 'utf-8'));
    await loginAsSuperAdmin(page);
    await addItemsToInventory(page, items);
});




