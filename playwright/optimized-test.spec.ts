import { test, expect, Page, BrowserContext } from '@playwright/test';
import { TestUtils, TestDataGenerator, StateValidator } from './test-utils';
import { promises as fs } from 'fs';

// --- State Management (from optimized-test.spec.ts) ---
interface TestState {
  loginCompleted: boolean;
  planCreated: boolean;
  organizationCreated: boolean;
  organizationActivated: boolean;
  branchesActivated: boolean;
  newBranchCreated: boolean;
  newBranchActivated: boolean;
  planName?: string;
  organizationName?: string;
  organizationActivationKey?: string;
  firstBranchActivationKey?: string;
  newBranchActivationKey?: string;
  newBranchName?: string;
  testStartTime?: string;
  lastUpdateTime?: string;
}
const STATE_FILE = 'test-state.json';
const BASE_URL = 'http://restaurent-management-sysytem.test';

class EnhancedTestStateManager {
  private state: TestState = {
    loginCompleted: false,
    planCreated: false,
    organizationCreated: false,
    organizationActivated: false,
    branchesActivated: false,
    newBranchCreated: false,
    newBranchActivated: false
  };

  async loadState(): Promise<void> {
    try {
      const stateData = await fs.readFile(STATE_FILE, 'utf8');
      this.state = { ...this.state, ...JSON.parse(stateData) };
      console.log('📚 Loaded test state:', this.state);
    } catch {
      this.state.testStartTime = new Date().toISOString();
    }
  }
  async saveState(): Promise<void> {
    this.state.lastUpdateTime = new Date().toISOString();
    await fs.writeFile(STATE_FILE, JSON.stringify(this.state, null, 2));
  }
  getState(): TestState { return this.state; }
  updateState(updates: Partial<TestState>): void { this.state = { ...this.state, ...updates }; }
  shouldSkipTest(requiredFields: string[]): boolean {
    return !StateValidator.validateState(this.state, requiredFields);
  }
}

// --- Main Test ---
test.describe('💡 Restaurant Management System - End-to-End Flow', () => {
  let stateManager: EnhancedTestStateManager;
  let page: Page;
  let context: BrowserContext;
  let testUtils: TestUtils;

  test.beforeAll(async ({ browser }) => {
    stateManager = new EnhancedTestStateManager();
    await stateManager.loadState();
    context = await browser.newContext({ viewport: { width: 1920, height: 1080 }, deviceScaleFactor: 0.8 });
    page = await context.newPage();
    testUtils = new TestUtils(page);
    page.setDefaultTimeout(30000);
  });

  test.afterAll(async () => { await context.close(); });

  test('Full Admin Flow', async () => {
    // --- Login ---
    if (!stateManager.getState().loginCompleted) {
      await page.goto(`${BASE_URL}/admin/login`);
      await testUtils.waitForPageLoad('login');
      await expect(page.locator('h1, h2, .text-3xl')).toContainText(/admin login/i);
      await testUtils.fillFormFieldSafely('input[name="email"]', 'superadmin@rms.com');
      await testUtils.fillFormFieldSafely('input[name="password"]', 'SuperAdmin123!');
      await testUtils.clickButtonSafely('Login');
      await page.waitForURL(/\/admin\/dashboard/, { timeout: 15000 });
      await testUtils.waitForPageLoad('dashboard');
      stateManager.updateState({ loginCompleted: true });
      await stateManager.saveState();
    }

    // --- Create Plan ---
    if (!stateManager.getState().planCreated) {
      await testUtils.navigateToSection('subscription-plans');
      await testUtils.waitForPageLoad('subscription-plans');
      const planName = 'Pro Plan';
      const existingPlan = await page.locator(`text="${planName}"`).count();
      if (existingPlan === 0) {
        await testUtils.clickButtonSafely('Create Plan');
        await testUtils.fillFormFieldSafely('input[name="name"]', planName);
        await testUtils.fillFormFieldSafely('textarea[name="description"]', 'Plan With Full Modules');
        // Select modules
        const modules = [
          'Reservation System Table',
          'Inventory Management Stock',
          'Menu Management Menu items,',
          'Customer Management Customer',
          'Kitchen Operations Kitchen',
          'Reports & Analytics Business',
          'System Administration System'
        ];
        for (const module of modules) {
          try {
            await page.getByRole('checkbox', { name: module }).check();
          } catch {}
        }
        await testUtils.fillFormFieldSafely('input[name=\"price\"]', '35000.00');
        await testUtils.clickButtonSafely('Create Plan');
        await testUtils.waitForSuccessMessage();
      }
      stateManager.updateState({ planCreated: true, planName });
      await stateManager.saveState();
    }

    // --- Create Organization ---
    if (!stateManager.getState().organizationCreated) {
      await testUtils.navigateToSection('organizations');
      await testUtils.waitForPageLoad('organizations');
      const orgName = 'Test-Organization';
      const email = 'torg@mail.com';
      await testUtils.clickButtonSafely('Add Organization');
      await testUtils.fillFormFieldSafely('input[name="name"]', orgName);
      await testUtils.fillFormFieldSafely('input[name="email"]', email);
      await testUtils.fillFormFieldSafely('input[name="phone"]', '0712345678');
      await testUtils.fillFormFieldSafely('input[name="password"]', 'Password@123');
      await testUtils.fillFormFieldSafely('input[name="password_confirmation"]', 'Password@123');
      await testUtils.fillFormFieldSafely('textarea[name="address"]', 'addres-090');
      await testUtils.fillFormFieldSafely('input[name="contact_person"]', 'Ruwan Kumara');
      await testUtils.fillFormFieldSafely('input[name="contact_person_designation"]', 'Owner');
      await testUtils.fillFormFieldSafely('input[name="contact_person_phone"]', '0712345678');
      await page.selectOption('select[name="is_active"]', '1');
      await testUtils.clickButtonSafely('Create Organization');
      await testUtils.waitForSuccessMessage();
      stateManager.updateState({ organizationCreated: true, organizationName: orgName });
      await stateManager.saveState();
    }

    // --- Activate First Branch ---
    if (!stateManager.getState().branchesActivated) {
      await testUtils.navigateToSection('branches');
      await testUtils.waitForPageLoad('branches');
      const firstBranchRow = page.locator('tbody tr').first();
      await firstBranchRow.locator('a:has-text("View")').click();
      const branchKey = await testUtils.copyActivationKey();
      await testUtils.navigateToSection('branches');
      await testUtils.clickButtonSafely('Activate Branch');
      await testUtils.fillFormFieldSafely('input[type=\"text\"], textarea', branchKey);
      await testUtils.clickButtonSafely('Activate');
      await testUtils.waitForSuccessMessage();
      stateManager.updateState({ branchesActivated: true, firstBranchActivationKey: branchKey });
      await stateManager.saveState();
    }

    // --- Create New Branch ---
    if (!stateManager.getState().newBranchCreated) {
      await testUtils.navigateToSection('branches');
      await testUtils.clickButtonSafely('Add Branch');
      const newBranchName = 'Second Branch';
      await testUtils.fillFormFieldSafely('input[name=\"name\"]', newBranchName);
      await testUtils.fillFormFieldSafely('input[name=\"address\"], textarea[name=\"address\"]', 'Address @ second branch');
      await testUtils.fillFormFieldSafely('input[name=\"phone\"]', '0712345678');
      await testUtils.fillFormFieldSafely('input[name=\"opening_time\"]', '08:00');
      await testUtils.fillFormFieldSafely('input[name=\"closing_time\"]', '22:00');
      await testUtils.fillFormFieldSafely('input[name=\"seating_capacity\"]', '60');
      await testUtils.fillFormFieldSafely('input[name=\"service_charge_percentage\"]', '10.00');
      await testUtils.fillFormFieldSafely('input[name=\"tax_percentage\"]', '5.00');
      await testUtils.fillFormFieldSafely('input[name=\"contact_person\"]', 'Sec Contact');
      await testUtils.fillFormFieldSafely('input[name=\"contact_person_designation\"]', 'Manager');
      await testUtils.fillFormFieldSafely('input[name=\"contact_person_phone\"]', '0712345678');
      await testUtils.clickButtonSafely('Create Branch');
      await testUtils.waitForSuccessMessage();
      stateManager.updateState({ newBranchCreated: true, newBranchName });
      await stateManager.saveState();
    }

    // --- Activate New Branch ---
    if (!stateManager.getState().newBranchActivated) {
      await testUtils.navigateToSection('branches');
      await testUtils.clickRowAction(stateManager.getState().newBranchName!, 'View');
      const newBranchKey = await testUtils.copyActivationKey();
      await testUtils.navigateToSection('branches');
      await testUtils.clickButtonSafely('Activate Branch');
      await testUtils.fillFormFieldSafely('input[type=\"text\"], textarea', newBranchKey);
      await testUtils.clickButtonSafely('Activate');
      await testUtils.waitForSuccessMessage();
      stateManager.updateState({ newBranchActivated: true, newBranchActivationKey: newBranchKey });
      await stateManager.saveState();
    }

    // --- Add User, Supplier, Inventory Items (as in your original script) ---
    // You can continue using testUtils and the same approach for these steps,
    // or modularize further as needed for maintainability.

    // ... (Add user, supplier, inventory item creation steps here, using testUtils)
  });
});
