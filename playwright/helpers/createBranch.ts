import { Page } from '@playwright/test';

export async function createBranch(page: Page,
    branchData: {
    organizationId?: string; // Optional, defaults to '1'
  name: string;
  address: string;
  phone: string;
  opening_time: string;
  closing_time: string;
  total_capacity: number;
  reservation_fee: number;
  cancellation_fee: number;
  contact_person: string;
  contact_person_designation: string;
  contact_person_phone: string;
  tables: { table_id: number; capacity: number }[];
}) {
  // Go to the branch creation page
  await page.goto(`https://restaurant-management-system.test/admin/organizations/${branchData.organizationId || '1'}/branches/create`);

  // Fill out branch details
  await page.fill('input[name="name"]', branchData.name);
  await page.fill('input[name="address"]', branchData.address);
  await page.fill('input[name="phone"]', branchData.phone);
  await page.fill('input[name="opening_time"]', branchData.opening_time);
  await page.fill('input[name="closing_time"]', branchData.closing_time);
  await page.fill('input[name="total_capacity"]', branchData.total_capacity.toString());
  await page.fill('input[name="reservation_fee"]', branchData.reservation_fee.toString());
  await page.fill('input[name="cancellation_fee"]', branchData.cancellation_fee.toString());
  await page.fill('input[name="contact_person"]', branchData.contact_person);
  await page.fill('input[name="contact_person_designation"]', branchData.contact_person_designation);
  await page.fill('input[name="contact_person_phone"]', branchData.contact_person_phone);

  // Fill tables
  // First table row is already present
  if (branchData.tables.length > 0) {
    await page.fill('input[name="tables[0][table_id]"]', branchData.tables[0].table_id.toString());
    await page.fill('input[name="tables[0][capacity]"]', branchData.tables[0].capacity.toString());
  }
  // Add more tables if needed
  for (let i = 1; i < branchData.tables.length; i++) {
    await page.click('#add-table-row');
    await page.fill(`input[name="tables[${i}][table_id]"]`, branchData.tables[i].table_id.toString());
    await page.fill(`input[name="tables[${i}][capacity]"]`, branchData.tables[i].capacity.toString());
  }

  // Submit the form
  await page.click('button[type="submit"]');
}
