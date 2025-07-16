
import { Page } from '@playwright/test';
import fs from 'fs';
import { loginAsSuperAdmin } from './loginAsSuperAdmin';

type ItemInput = {
  name: string;
  category: string;
  unit: string;
  buyingPrice: string;
  sellingPrice: string;
  stockLevel: string;
};

export async function addItemsToInventory(page: Page, itemsOrCount: number | ItemInput[]) {
  // Assume login is handled outside for test speed
  let items: ItemInput[] = [];
  if (typeof itemsOrCount === 'number') {
    for (let i = 0; i < itemsOrCount; i++) {
      items.push({
        name: `Item ${i + 1}`,
        category: ((i % 3) + 1).toString(),
        unit: 'piece',
        buyingPrice: '656',
        sellingPrice: '566',
        stockLevel: '0',
      });
    }
  } else {
    items = itemsOrCount;
  }

  for (let i = 0; i < items.length; i++) {
    const item = items[i];
    await page.goto('https://restaurant-management-system.test/admin/inventory/items/create', { timeout: 60000 });
    // Wait for the heading to ensure the page is loaded
    await page.getByRole('heading', { name: 'Add New Items', level: 2 }).waitFor({ timeout: 15000 });

    await page.getByLabel('Target Organization *').selectOption('1');
    await page.getByRole('textbox', { name: 'Enter item name' }).fill(item.name);
    await page.getByRole('textbox', { name: 'Enter unicode name' }).fill(`U${i + 1}`);
    const randomCode = Math.floor(100000 + Math.random() * 900000).toString();
    await page.getByRole('textbox', { name: 'Enter item code' }).fill(randomCode);
    await page.getByPlaceholder('Minimum stock level').fill('3');
    await page.locator('select[name="items[0][unit_of_measurement]"]').selectOption(item.unit);
    // Keep track of the current category id between test runs
    let categoryId = (i % 3) + 1; // Cycle through 1, 2, 3
    await page.locator('select[name="items[0][item_category_id]"]').selectOption(categoryId.toString());
    await page.locator('input[name="items[0][buying_price]"]').fill(item.buyingPrice);
    await page.locator('input[name="items[0][selling_price]"]').fill(item.sellingPrice);
    await page.getByPlaceholder('Expiry period in days').fill('66');
    await page.getByRole('textbox', { name: 'Detailed item description' }).fill('666');
    await page.getByRole('textbox', { name: 'Any special notes about this' }).fill('666');
    await page.locator('select[name="items[0][item_type]"]').selectOption('buy_sell');
    await page.locator('input[name="items[0][current_stock]"]').fill(item.stockLevel);
    await page.getByRole('button', { name: ' Save All Items' }).click();
    // Wait for navigation or success message if needed
    await page.waitForTimeout(300); // Short wait for UI stability
  }
}
