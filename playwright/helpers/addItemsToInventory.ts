

import { Page } from '@playwright/test';

export type InventoryItem = {
  name: string;
  unicodeName: string;
  code?: string;
  category?: string; // Optional, for future use
  unit?: string;
  buyingPrice?: string;
  sellingPrice?: string;
  stockLevel?: string;
};

/**
 * Add one or more items to inventory via Playwright UI automation.
 * Supports super admin org/category selection and retry on submit.
 */
export async function addItemsToInventory(page: Page, itemsOrCount: number | InventoryItem | InventoryItem[]) {
  // Normalize input to array of InventoryItem
  let items: InventoryItem[] = [];
  if (typeof itemsOrCount === 'number') {
    // ... (keep existing dummy item generation logic)
    for (let i = 0; i < itemsOrCount; i++) {
      items.push({
        name: `Item ${i + 1}`,
        unicodeName: `U${i + 1}`,
        code: (Math.floor(100000 + Math.random() * 900000)).toString(),
        unit: 'piece',
        buyingPrice: '656',
        sellingPrice: '566',
        stockLevel: '0',
      });
    }
  } else if (Array.isArray(itemsOrCount)) {
    items = itemsOrCount;
  } else {
    items = [itemsOrCount];
  }

  for (let i = 0; i < items.length; i++) {
    const item = items[i];
    console.log('Navigating to create page for item:', item.name);
    try {
      await page.goto('https://restaurant-management-system.test/admin/inventory/items/create', {
        waitUntil: 'domcontentloaded',
        timeout: 30000
      });

      // Wait for form to be ready
      await page.waitForSelector('#items-form', { state: 'attached', timeout: 20000 });
      await page.waitForLoadState('networkidle');

      // Super admin: select organization if visible
      if (await page.locator('#organization_id').isVisible({ timeout: 5000 })) {
        console.log('Selecting organization for item:', item.name);
        await page.selectOption('#organization_id', { label: 'Delicious Bites Restaurant' });
        await page.waitForResponse(/\/admin\/api\/organizations\/.*\/categories/);
      }

      console.log('Filling form for item:', item.name);

      // Fill item details with proper waits
      await fillFieldWithRetry(page, 'input[name="items[0][name]"]', item.name);
      await fillFieldWithRetry(page, 'input[name="items[0][unicode_name]"]', item.unicodeName);
      await fillFieldWithRetry(page, 'input[name="items[0][item_code]"]', item.code || '');

      // Wait for categories to load
      await page.waitForSelector('select[name="items[0][item_category_id]"] option:not([value=""])', { timeout: 10000 });
      if (item.category) {
        await page.selectOption('select[name="items[0][item_category_id]"]', { value: item.category });
      } else {
        // Select first available category by value
        const options = await page.$$eval('select[name="items[0][item_category_id]"] option', opts => (opts as HTMLOptionElement[]).map(o => o.value).filter(v => v));
        if (options.length > 0) {
          await page.selectOption('select[name="items[0][item_category_id]"]', options[0]);
        }
      }

      // Fill other fields
      await fillFieldWithRetry(page, 'input[name="items[0][buying_price]"]', item.buyingPrice || '656');
      await fillFieldWithRetry(page, 'input[name="items[0][selling_price]"]', item.sellingPrice || '566');
      await fillFieldWithRetry(page, 'input[name="items[0][current_stock]"]', item.stockLevel || '0');

      // Unit of measurement (select by label or value)
      if (item.unit) {
        // Try by label first, fallback to value
        try {
          await page.selectOption('select[name="items[0][unit_of_measurement]"]', { label: item.unit });
        } catch {
          // Try by value (e.g., 'kg', 'pcs', etc.)
          const unitValue = (item.unit.match(/\(([^)]+)\)/) || [])[1];
          if (unitValue) {
            await page.selectOption('select[name="items[0][unit_of_measurement]"]', { value: unitValue });
          }
        }
      }

      // Submit with better handling
      const saveButton = page.getByRole('button', { name: ' Save All Items' });
      await saveButton.click();

      // Wait for either success or form error
      await Promise.race([
        page.waitForURL(/\/admin\/inventory\/items/, { timeout: 10000 }),
        page.waitForSelector('.alert-error, .alert-success', { timeout: 10000 })
      ]);

    } catch (error) {
      console.error(`Error processing item ${item.name}:`, error);
      // Take screenshot on error
      await page.screenshot({ path: `error-${item.name.replace(/[^a-z0-9]/gi, '_')}.png` });
      throw error;
    }
  }
}

async function fillFieldWithRetry(page: Page, selector: string, value: string, retries = 3) {
  for (let i = 0; i < retries; i++) {
    try {
      await page.waitForSelector(selector, { state: 'visible', timeout: 5000 });
      await page.fill(selector, value);
      return;
    } catch (error) {
      if (i === retries - 1) throw error;
      await page.waitForTimeout(1000);
    }
  }
}
