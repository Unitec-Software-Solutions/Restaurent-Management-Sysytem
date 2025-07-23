import { Page } from '@playwright/test';

export type InventoryItem = {
  name: string;
  unicodeName: string;
  code?: string;
  category?: string;
  unit?: string;
  buyingPrice?: string;
  sellingPrice?: string;
  itemType?: string;
  stockLevel?: string;
  shelfLifeInDays?: string;
  description?: string;
  additionalNotes?: string;
  isPerishable?: boolean;
  isMenuItem?: boolean;
  requiresProduction?: boolean;
  isInventoryItem?: boolean;
  menuAttributes?: {
    cuisineType?: string;
    spiceLevel?: string;
    prepTimeMinutes?: string;
    servingSize?: string;
    dietaryType?: string;
    availability?: string;
    mainIngredients?: string;
    allergenInfo?: string;
    isChefsSpecial?: boolean;
    isPopular?: boolean;
  };
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

  const testRunId = process.env.TEST_RUN_ID || 'default';
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
      // await page.waitForLoadState('networkidle');

      // Super admin: select organization if visible
      if (await page.locator('#organization_id').isVisible({ timeout: 5000 })) {
        console.log('Selecting organization for item:', item.name);
        await page.selectOption('#organization_id', { label: 'Delicious Bites Restaurant' });
        try {
          await page.waitForResponse(/\/admin\/api\/organizations\/.*\/categories/, { timeout: 10000 });
        } catch (err) {
          console.warn('Timeout or browser closed waiting for categories API response for item:', item.name);
          // Optionally, continue or break depending on your workflow
          // break; // If you want to stop on error
        }
      }

      console.log('Filling form for item:', item.name);

      // Fill item details with proper waits
      await fillFieldWithRetry(page, 'input[name="items[0][name]"]', item.name);
      await fillFieldWithRetry(page, 'input[name="items[0][unicode_name]"]', item.unicodeName);
      await fillFieldWithRetry(page, 'input[name="items[0][item_code]"]', item.code || '');

      // Wait for categories to load
      await page.waitForSelector('select[name="items[0][item_category_id]"]:not([disabled])', { timeout: 10000 });
      // Optionally, ensure there are options to select
      await page.waitForFunction(
        (sel) => {
          const select = document.querySelector(sel) as HTMLSelectElement;
          return select && Array.from(select.options).some(o => o.value);
        },
        'select[name="items[0][item_category_id]"]',
        { timeout: 10000 }
      );
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
      await fillFieldWithRetry(page, 'input[name="items[0][reorder_level]"]', '10');
      await fillFieldWithRetry(page, 'input[name="items[0][current_stock]"]', item.stockLevel || '0');
      if (item.shelfLifeInDays) {
        await fillFieldWithRetry(page, 'input[name="items[0][shelf_life_in_days]"]', item.shelfLifeInDays);
      }
      if (item.description) {
        await fillFieldWithRetry(page, 'textarea[name="items[0][description]"]', item.description);
      }
      if (item.additionalNotes) {
        await fillFieldWithRetry(page, 'textarea[name="items[0][additional_notes]"]', item.additionalNotes);
      }

      // Item type
      if (item.itemType) {
        await page.selectOption('select[name="items[0][item_type]"]', item.itemType);
      }

      // Perishable
      if (typeof item.isPerishable === 'boolean') {
        const perishableCheckbox = page.locator('input[name="items[0][is_perishable]"][type="checkbox"]');
        if (await perishableCheckbox.isVisible()) {
          const checked = await perishableCheckbox.isChecked();
          if (checked !== item.isPerishable) {
            await perishableCheckbox.setChecked(item.isPerishable);
          }
        }
      }
      // Is Menu Item
      if (typeof item.isMenuItem === 'boolean') {
        const menuItemCheckbox = page.locator('input[name="items[0][is_menu_item]"][type="checkbox"]');
        if (await menuItemCheckbox.isVisible()) {
          const checked = await menuItemCheckbox.isChecked();
          if (checked !== item.isMenuItem) {
            await menuItemCheckbox.setChecked(item.isMenuItem);
          }
        }
      }
      // Requires Production
      if (typeof item.requiresProduction === 'boolean') {
        const prodCheckbox = page.locator('input[name="items[0][requires_production]"][type="checkbox"]');
        if (await prodCheckbox.isVisible()) {
          const checked = await prodCheckbox.isChecked();
          if (checked !== item.requiresProduction) {
            await prodCheckbox.setChecked(item.requiresProduction);
          }
        }
      }
      // Track Inventory
      if (typeof item.isInventoryItem === 'boolean') {
        const invCheckbox = page.locator('input[name="items[0][is_inventory_item]"][type="checkbox"]');
        if (await invCheckbox.isVisible()) {
          const checked = await invCheckbox.isChecked();
          if (checked !== item.isInventoryItem) {
            await invCheckbox.setChecked(item.isInventoryItem);
          }
        }
      }

      // Menu attributes
      if (item.menuAttributes) {
        const attr = item.menuAttributes;
        if (attr.cuisineType) await page.selectOption('select[data-menu-attr="cuisine_type"]', attr.cuisineType);
        if (attr.spiceLevel) await page.selectOption('select[data-menu-attr="spice_level"]', attr.spiceLevel);
        if (attr.prepTimeMinutes) await fillFieldWithRetry(page, 'input[data-menu-attr="prep_time_minutes"]', attr.prepTimeMinutes);
        if (attr.servingSize) await page.selectOption('select[data-menu-attr="serving_size"]', attr.servingSize);
        if (attr.dietaryType) await page.selectOption('select[data-menu-attr="dietary_type"]', attr.dietaryType);
        if (attr.availability) await page.selectOption('select[data-menu-attr="availability"]', attr.availability);
        if (attr.mainIngredients) await fillFieldWithRetry(page, 'textarea[data-menu-attr="main_ingredients"]', attr.mainIngredients);
        if (attr.allergenInfo) await fillFieldWithRetry(page, 'textarea[data-menu-attr="allergen_info"]', attr.allergenInfo);
        if (typeof attr.isChefsSpecial === 'boolean') {
          const chefCheckbox = page.locator('input[data-menu-attr="is_chefs_special"]');
          if (await chefCheckbox.isVisible()) {
            const checked = await chefCheckbox.isChecked();
            if (checked !== attr.isChefsSpecial) {
              await chefCheckbox.setChecked(attr.isChefsSpecial);
            }
          }
        }
        if (typeof attr.isPopular === 'boolean') {
          const popCheckbox = page.locator('input[data-menu-attr="is_popular"]');
          if (await popCheckbox.isVisible()) {
            const checked = await popCheckbox.isChecked();
            if (checked !== attr.isPopular) {
              await popCheckbox.setChecked(attr.isPopular);
            }
          }
        }
      }

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
      const saveButton = page.getByRole('button', { name: '\uf0c7 Save All Items' });
      // Scroll into view and wait for stability
      await saveButton.scrollIntoViewIfNeeded();
      await saveButton.waitFor({ state: 'visible', timeout: 10000 });
      // Wait for button to not be disabled
      await page.waitForFunction(
        (el) => {
          if (!el) return false;
          // Only check disabled for HTMLButtonElement
          if (el instanceof HTMLButtonElement) {
            return !el.disabled;
          }
          // If not a button, assume enabled
          return true;
        },
        await saveButton.elementHandle(),
        { timeout: 10000 }
      );
      // Wait for stability (no animation/movement)
      await page.waitForTimeout(500); // Give time for layout/animation
      await saveButton.click({ timeout: 20000 });

      // Wait for either success or form error
      await Promise.race([
        page.waitForURL(/\/admin\/inventory\/items/, { timeout: 15000 }),
        page.waitForSelector('.alert-error, .alert-success', { timeout: 15000 })
      ]);

    } catch (error) {
      console.error(`Error processing item ${item.name}:`, error);
      // Take screenshot on error
      if (page && typeof page.screenshot === 'function') {
        await page.screenshot({ path: `test-results/screen-shots/${testRunId}/error-${item.name.replace(/[^a-z0-9]/gi, '_')}.png` });
      }
      // Optionally rethrow or continue
      // throw error;
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
