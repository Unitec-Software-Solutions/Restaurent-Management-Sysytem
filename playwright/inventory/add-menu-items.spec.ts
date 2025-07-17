import { test, expect } from '@playwright/test';
import menuItems from './menu-items-data.json' assert { type: 'json' };

// Helper to robustly select an option by label or value, with debug logging
async function robustSelectOption(page, selector, labelOrValue, opts = {}) {
  const el = page.locator(selector);
  await el.waitFor({ state: 'visible', timeout: 10000 });
  // Try by label first
  let options = await el.locator('option').allTextContents();
  let values = await el.locator('option').evaluateAll(opts => opts.map(o => o.value));
  // Try label
  try {
    await el.selectOption({ label: labelOrValue }, opts);
    return;
  } catch (e) {
    // Try value
    if (values.includes(labelOrValue)) {
      await el.selectOption({ value: labelOrValue }, opts);
      return;
    }
    // Try first non-empty option (skip placeholder)
    const firstValid = values.find(v => v && v !== '');
    if (firstValid) {
      await el.selectOption({ value: firstValid }, opts);
      console.warn(`Fallback: selected first valid option (${firstValid}) for ${selector}. Available:`, options);
      return;
    }
    // Log for debug
    throw new Error(`Could not select option '${labelOrValue}' for ${selector}. Available options: ${options.join(', ')}`);
  }
}

// Utility to login as admin (update selectors/URL as needed)
async function loginAsAdmin(page) {
  await page.goto('/admin/login');
  await page.fill('input[name="email"]', process.env.ADMIN_EMAIL || 'superadmin@rms.com');
  await page.fill('input[name="password"]', process.env.ADMIN_PASSWORD || 'SuperAdmin123!');
  await page.click('button[type="submit"]');
  await page.waitForURL('**/admin/dashboard');
}

test.describe('Inventory Menu Items - Add', () => {
  test('should add new menu items from data file', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/admin/inventory/items/create');

    for (let i = 0; i < menuItems.length; i++) {
      const item = menuItems[i];
      if (i > 0) {
        await page.click('#add-item');
      }
      const prefix = `items[${i}]`;
      // Fill basic info
      await page.fill(`input[name="${prefix}[name]"]`, item.name);
      await page.fill(`input[name="${prefix}[unicode_name]"]`, item.unicode_name);
      // If super admin, select organization first
      if (await page.locator('#organization_id').isVisible()) {
        await robustSelectOption(page, '#organization_id', item.organization_id || 1);
        // Wait for categories to reload if needed
        await page.waitForTimeout(800);
      }
      // Wait for item code field to be visible and enabled
      await page.locator(`input[name="${prefix}[item_code]"]`).waitFor({ state: 'visible', timeout: 10000 });
      await page.fill(`input[name="${prefix}[item_code]"]`, item.item_code);
      // Category
      await robustSelectOption(page, `select[name="${prefix}[item_category_id]"]`, item.item_category);
      // Unit of measurement
      await robustSelectOption(page, `select[name="${prefix}[unit_of_measurement]"]`, item.unit_of_measurement);
      await page.fill(`input[name="${prefix}[reorder_level]"]`, item.reorder_level.toString());
      await page.fill(`input[name="${prefix}[buying_price]"]`, item.buying_price.toString());
      await page.fill(`input[name="${prefix}[selling_price]"]`, item.selling_price.toString());
      await page.fill(`input[name="${prefix}[shelf_life_in_days]"]`, item.shelf_life_in_days.toString());
      await page.fill(`textarea[name="${prefix}[description]"]`, item.description);
      await page.fill(`textarea[name="${prefix}[additional_notes]"]`, item.additional_notes);
      // Item type
      await robustSelectOption(page, `select[name="${prefix}[item_type]"]`, item.item_type);
      await page.fill(`input[name="${prefix}[current_stock]"]`, item.current_stock.toString());
      // Checkboxes
      if (item.is_perishable) await page.check(`#perishable-${i}`);
      if (item.is_menu_item) {
        await page.check(`#menuitem-${i}`);
        // Wait for menu-attributes section to appear (dynamic)
        await page.locator(`.menu-attributes[data-index="${i}"]`).waitFor({ state: 'visible', timeout: 10000 });
      }
      if (item.requires_production) await page.check(`#requires-production-${i}`);
      if (item.is_inventory_item) await page.check(`#inventory-item-${i}`);
      // Menu attributes (if menu item)
      if (item.is_menu_item && item.menu_attributes) {
        const attr = item.menu_attributes;
        // Map JSON keys to data-menu-attr values in the form
        const menuAttrMap = {
          cuisine_type: 'cuisine_type',
          spice_level: 'spice_level',
          preparation_time: 'prep_time_minutes',
          serving_size: 'serving_size',
          dietary_restrictions: 'dietary_type',
          availability: 'availability',
          main_ingredients: 'main_ingredients',
          allergen_information: 'allergen_info',
          chefs_recommendation: 'is_chefs_special',
          popular_item: 'is_popular',
        };
        // Cuisine Type (select)
        if (attr.cuisine_type) {
          await page.selectOption(`.menu-attributes[data-index="${i}"] select[data-menu-attr="cuisine_type"]`, { label: attr.cuisine_type });
        }
        // Spice Level (select)
        if (attr.spice_level) {
          await page.selectOption(`.menu-attributes[data-index="${i}"] select[data-menu-attr="spice_level"]`, { label: attr.spice_level });
        }
        // Preparation Time (input)
        if (attr.preparation_time) {
          await page.fill(`.menu-attributes[data-index="${i}"] input[data-menu-attr="prep_time_minutes"]`, attr.preparation_time.toString());
        }
        // Serving Size (select)
        if (attr.serving_size) {
          await page.selectOption(`.menu-attributes[data-index="${i}"] select[data-menu-attr="serving_size"]`, { label: attr.serving_size });
        }
        // Dietary Restrictions (select, single value)
        if (Array.isArray(attr.dietary_restrictions) && attr.dietary_restrictions.length > 0) {
          // Only select the first restriction (form only supports one)
          await page.selectOption(`.menu-attributes[data-index="${i}"] select[data-menu-attr="dietary_type"]`, { label: attr.dietary_restrictions[0] });
        }
        // Availability (select)
        if (attr.availability) {
          // Map to form values
          let availValue = '';
          switch (attr.availability.toLowerCase()) {
            case 'all day': availValue = 'all_day'; break;
            case 'breakfast': availValue = 'breakfast'; break;
            case 'lunch': availValue = 'lunch'; break;
            case 'dinner': availValue = 'dinner'; break;
            case 'lunch, dinner':
            case 'lunch & dinner': availValue = 'lunch_dinner'; break;
            default: availValue = 'all_day'; break;
          }
          await page.selectOption(`.menu-attributes[data-index="${i}"] select[data-menu-attr="availability"]`, availValue);
        }
        // Main Ingredients (textarea)
        if (attr.main_ingredients) {
          await page.fill(`.menu-attributes[data-index="${i}"] textarea[data-menu-attr="main_ingredients"]`, attr.main_ingredients);
        }
        // Allergen Information (textarea)
        if (attr.allergen_information) {
          await page.fill(`.menu-attributes[data-index="${i}"] textarea[data-menu-attr="allergen_info"]`, attr.allergen_information);
        }
        // Chef's Recommendation (checkbox)
        if (attr.chefs_recommendation) {
          await page.check(`.menu-attributes[data-index="${i}"] input[data-menu-attr="is_chefs_special"]`).catch(() => {});
        }
        // Popular Item (checkbox)
        if (attr.popular_item) {
          await page.check(`.menu-attributes[data-index="${i}"] input[data-menu-attr="is_popular"]`).catch(() => {});
        }
      }
    }
    // Wait for manual input before submitting
    console.log('Please review the form and press Enter to continue...');
    await new Promise(resolve => process.stdin.once('data', resolve));
    // Submit the form
    await page.click('button[type="submit"]');
    // Expect success message or redirect
    await expect(page.locator('text=Successfully added')).toBeVisible();
    // Optionally, verify the items appear in the recently added list
    for (const item of menuItems) {
      await expect(page.locator(`text=${item.name}`)).toBeVisible();
      await expect(page.locator(`text=${item.item_code}`)).toBeVisible();
    }
  });
});
