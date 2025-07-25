import { Page } from '@playwright/test';

export async function createMenuCategory(page: Page, {
    name = 'Breakfast',
    sort_order = 1,
    description = 'Morning Meal',
    image_url = 'https://www.premierinn.com/content/dam/pi/websites/desktop/why/Food/2025/ss25/xcooked-breakfast-1000x640.jpg.pagespeed.ic.apMt5gCIR7.jpg',
    is_active = true,
    is_featured = true
} = {}) {
    await page.goto('https://rms-unitec.test/admin/menu-categories/create');
    await page.fill('#name', name);
    await page.fill('#sort_order', sort_order.toString());
    await page.fill('#description', description);
    await page.fill('#image_url', image_url);

    // Set checkboxes
    if (is_active) {
        await page.check('#is_active');
    } else {
        await page.uncheck('#is_active');
    }
    if (is_featured) {
        await page.check('#is_featured');
    } else {
        await page.uncheck('#is_featured');
    }

    await page.click('button[type="submit"]');
}