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
import { createBranch } from './helpers/createBranch';
import { createMenuCategory } from './helpers/createMenuCategory';

test.skip('Admin Workflow 🚀', async ({ page }) => {
    await loginAsSuperAdmin(page);
    await createSubscriptionPlan(page);
    //await createOrganization(page);
});
test('Create Subscription Plan 🚀', async ({ page }) => {
    await loginAsSuperAdmin(page);
    await createSubscriptionPlan(page);
    //await createOrganization(page);
});
test('Create Organization 🏢', async ({ page }) => {
    await loginAsSuperAdmin(page);
    await createOrganization(page);
});

test('Activate Branch Default 🔑', async ({ page }) => {
    await activateBranch(page, { branchId: '1' });
});


test('Create New Branch 🏪', async ({ page }) => {
    await loginAsSuperAdmin(page);
    // create a new branch with default organization ID - 1 Branch id - 2
    await createBranch(page, {
        organizationId: '1', // Default organization ID
        name: 'Galle Branch',
        address: '123 Main St, Galle, Sri Lanka',
        phone: '0712345678',
        opening_time: '08:00',
        closing_time: '22:00',
        total_capacity: 50,
        reservation_fee: 100.00,
        cancellation_fee: 50.00,
        contact_person: 'John Doe',
        contact_person_designation: 'Manager',
        contact_person_phone: '0712345678',
        tables: [
            { table_id: 1, capacity: 4 }
        ]
    });

    // Activate the branch after creation
    await activateBranch(page, { branchId: '2' });

    // create branch 3
    await createBranch(page, {
        organizationId: '1', // Default organization ID
        name: 'Negambo Branch',
        address: '123 Main St, Negambo, Sri Lanka',
        phone: '0712345678',
        opening_time: '08:00',
        closing_time: '22:00',
        total_capacity: 50,
        reservation_fee: 100.00,
        cancellation_fee: 50.00,
        contact_person: 'John Doe',
        contact_person_designation: 'Manager',
        contact_person_phone: '0712345678',
        tables: [
            { table_id: 1, capacity: 4 }
        ]
    });

    // Activate the branch after creation
    await activateBranch(page, { branchId: '3' });
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

test('Create Breakfast Menu Category 🍳', async ({ page }) => {
    await loginAsSuperAdmin(page);
    await createMenuCategory(page);
});




