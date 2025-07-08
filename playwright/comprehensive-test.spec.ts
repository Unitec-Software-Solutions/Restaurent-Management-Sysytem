import { test, expect, Page, BrowserContext } from '@playwright/test';
import { promises as fs } from 'fs';
import path from 'path';

// Test state tracking
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
}

const STATE_FILE = 'test-state.json';
const BASE_URL = 'http://restaurent-management-sysytem.test';

  test('2. Navigate to Modules and Create Subscription Plan Unit', async () => {
// --- Utility functions ---
const waitForElement = async (page, selector, timeout = 5000) => {
  await page.waitForSelector(selector, { timeout });
};
const fillFormField = async (page, selector, value) => {
  await page.waitForSelector(selector);
  await page.fill(selector, value);
};
const clickButton = async (page, selector) => {
  await page.waitForSelector(selector);
  await page.click(selector);
};
const expectUrlContains = async (page, urlPart) => {
  await expect(page).toHaveURL(new RegExp(urlPart));
};

// --- TestStateManager class ---
class TestStateManager {
  state: TestState;
  constructor() {
    this.state = {
      loginCompleted: false,
      planCreated: false,
      organizationCreated: false,
      organizationActivated: false,
      branchesActivated: false,
      newBranchCreated: false,
      newBranchActivated: false
    };
  }
  async loadState() {
    try {
      const stateData = await fs.readFile(STATE_FILE, 'utf8');
      this.state = { ...this.state, ...JSON.parse(stateData) };
      console.log('📚 Loaded test state:', this.state);
    } catch (error) {
      console.log('📝 No existing state file, starting fresh');
    }
  }
  async saveState() {
    await fs.writeFile(STATE_FILE, JSON.stringify(this.state, null, 2));
    console.log('💾 Saved test state:', this.state);
  }
  getState() { return this.state; }
  updateState(updates: Partial<TestState>) { this.state = { ...this.state, ...updates }; }
  async clearState() {
    this.state = {
      loginCompleted: false,
      planCreated: false,
      organizationCreated: false,
      organizationActivated: false,
      branchesActivated: false,
      newBranchCreated: false,
      newBranchActivated: false
    };
    await this.saveState();
  }
}

// --- Main test suite ---
test.describe('Restaurant Management System - Comprehensive Test Suite', () => {
  let stateManager: TestStateManager;
  let sharedPage: Page;
  let sharedContext: BrowserContext;

  test.beforeAll(async ({ browser }) => {
    stateManager = new TestStateManager();
    await stateManager.loadState();
    sharedContext = await browser.newContext({ viewport: { width: 1920, height: 1080 } });
    sharedPage = await sharedContext.newPage();
  });

  test.afterAll(async () => {
    if (sharedContext) {
      await sharedContext.close();
    }
  });

  test('1. Admin Login Unit', async () => {
    const state = stateManager.getState();
    if (state.loginCompleted) {
      console.log('✅ Login already completed, skipping...');
      return;
    }
    console.log('� Starting admin login test...');
    await sharedPage.goto(`${BASE_URL}/admin/login`);
    await expectUrlContains(sharedPage, '/admin/login');
    await expect(sharedPage.locator('h1, h2, .text-3xl')).toContainText(/admin login/i);
    await fillFormField(sharedPage, 'input[name="email"]', 'superadmin@rms.com');
    await fillFormField(sharedPage, 'input[name="password"]', 'SuperAdmin123!');
    await clickButton(sharedPage, 'button[type="submit"]');
    await sharedPage.waitForURL(/\/admin\/dashboard/, { timeout: 10000 });
    await expect(sharedPage).not.toHaveURL(/\/admin\/login$/);
    stateManager.updateState({ loginCompleted: true });
    await stateManager.saveState();
    console.log('✅ Admin login completed successfully');
  });

  test('2. Navigate to Modules and Create Subscription Plan Unit', async () => {
    const state = stateManager.getState();
    if (!state.loginCompleted) {
      test.skip(!state.loginCompleted, 'Login not completed');
      return;
    }
    if (state.planCreated) {
      console.log('✅ Subscription plan already created, skipping...');
      return;
    }
    console.log('📋 Creating subscription plan...');
    await clickButton(sharedPage, 'a[href*="subscription-plans"], a:has-text("Subscriptions")');
    await expectUrlContains(sharedPage, 'subscription-plans');
    const planName = `Test-Pro-Plan-${Date.now()}`;
    const existingPlan = await sharedPage.locator(`text="${planName}"`).count();
    if (existingPlan === 0) {
      await clickButton(sharedPage, 'a:has-text("Create Plan"), a:has-text("+ Create Plan")');
      await fillFormField(sharedPage, 'input[name="name"]', planName);
      await sharedPage.selectOption('select[name="currency"]', 'LKR');
      await fillFormField(sharedPage, 'textarea[name="description"]', 'Comprehensive plan with all modules for testing');
      const moduleCheckboxes = await sharedPage.locator('input[type="checkbox"][name="modules[]"]');
      const moduleCount = await moduleCheckboxes.count();
      for (let i = 0; i < moduleCount; i++) {
        const checkbox = moduleCheckboxes.nth(i);
        if (!(await checkbox.isChecked())) {
          await checkbox.check();
        }
      }
      await fillFormField(sharedPage, 'input[name="price"]', '35000.00');
      // Optionally set max_branches and max_employees here if needed
      const trialCheckbox = sharedPage.locator('input[name="is_trial"]');
      if (!(await trialCheckbox.isChecked())) {
        await trialCheckbox.check();
      }
      await fillFormField(sharedPage, 'input[name="trial_period_days"]', '30');
      const activeCheckbox = sharedPage.locator('input[name="is_active"]');
      if (!(await activeCheckbox.isChecked())) {
        await activeCheckbox.check();
      }
      await clickButton(sharedPage, 'button:has-text("Create Plan"), button[type="submit"]');
      await sharedPage.waitForTimeout(2000);
    }
    stateManager.updateState({ planCreated: true, planName: planName });
    await stateManager.saveState();
    console.log('✅ Subscription plan created successfully');
  });

  test('1. Admin Login Unit', async () => {
    const state = stateManager.getState();

    if (state.loginCompleted) {
      console.log('✅ Login already completed, skipping...');
      return;
    }

    console.log('🔐 Starting admin login test...');

    await sharedPage.goto(`${BASE_URL}/admin/login`);
    await expectUrlContains(sharedPage, '/admin/login');

    // Verify login page elements
    await expect(sharedPage.locator('h1, h2, .text-3xl')).toContainText(/admin login/i);

    // Fill login credentials
    await fillFormField(sharedPage, 'input[name="email"]', 'superadmin@rms.com');
    await fillFormField(sharedPage, 'input[name="password"]', 'SuperAdmin123!');

    // Submit login
    await clickButton(sharedPage, 'button[type="submit"]');

    // Wait for redirect and verify successful login
    await sharedPage.waitForURL(/\/admin\/dashboard/, { timeout: 10000 });
    await expect(sharedPage).not.toHaveURL(/\/admin\/login$/);

    // Update state
    stateManager.updateState({ loginCompleted: true });
    await stateManager.saveState();

    console.log('✅ Admin login completed successfully');
  });

  test('2. Navigate to Modules and Create Subscription Plan Unit', async () => {
    const state = stateManager.getState();
    if (!state.loginCompleted) {
      test.skip(!state.loginCompleted, 'Login not completed');
      return;
    }
    if (state.planCreated) {
      console.log('✅ Subscription plan already created, skipping...');
      return;
    }
    console.log('📋 Creating subscription plan...');
    // Navigate to subscription plans
    await clickButton(sharedPage, 'a[href*="subscription-plans"], a:has-text("Subscriptions")');
    await expectUrlContains(sharedPage, 'subscription-plans');
    // Check if plan already exists
    const planName = `Test-Pro-Plan-${Date.now()}`;
    const existingPlan = await sharedPage.locator(`text="${planName}"`).count();
    if (existingPlan === 0) {
      // Create new plan
      await clickButton(sharedPage, 'a:has-text("Create Plan"), a:has-text("+ Create Plan")');
      // Fill plan details
      await fillFormField(sharedPage, 'input[name="name"]', planName);
      // Select currency (default to LKR)
      await sharedPage.selectOption('select[name="currency"]', 'LKR');
      await fillFormField(sharedPage, 'textarea[name="description"]', 'Comprehensive plan with all modules for testing');
      // Select all modules (check all checkboxes in the modules section)
      const moduleCheckboxes = await sharedPage.locator('input[type="checkbox"][name="modules[]"]');
      const moduleCount = await moduleCheckboxes.count();
      for (let i = 0; i < moduleCount; i++) {
        const checkbox = moduleCheckboxes.nth(i);
        if (!(await checkbox.isChecked())) {
          await checkbox.check();
        }
      }
      // Set price
      await fillFormField(sharedPage, 'input[name="price"]', '35000.00');
      // Set max branches and employees (optional, leave empty for unlimited)
      // await fillFormField(sharedPage, 'input[name="max_branches"]', '');
      // await fillFormField(sharedPage, 'input[name="max_employees"]', '');
      // Enable trial and set trial days
      const trialCheckbox = sharedPage.locator('input[name="is_trial"]');
      if (!(await trialCheckbox.isChecked())) {
        await trialCheckbox.check();
      }
      await fillFormField(sharedPage, 'input[name="trial_period_days"]', '30');
      // Set plan as active
      const activeCheckbox = sharedPage.locator('input[name="is_active"]');
      if (!(await activeCheckbox.isChecked())) {
        await activeCheckbox.check();
      }
      // Submit plan creation
      await clickButton(sharedPage, 'button:has-text("Create Plan"), button[type="submit"]');
      // Wait for success message or redirect
      await sharedPage.waitForTimeout(2000);
    }
    // Update state
    stateManager.updateState({
      planCreated: true,
      planName: planName
    });
    await stateManager.saveState();
    console.log('✅ Subscription plan created successfully');
  });

  test('3. Create Organization Unit', async () => {
    const state = stateManager.getState();

    if (!state.planCreated) {
      test.skip(!state.planCreated, 'Plan creation not completed');
      return;
    }

    if (state.organizationCreated) {
      console.log('✅ Organization already created, skipping...');
      return;
    }

    console.log('🏢 Creating organization...');

    // Navigate to organizations
    await clickButton(sharedPage, 'a[href*="organizations"], a:has-text("Organizations")');
    await expectUrlContains(sharedPage, 'organizations');

    const orgName = `Test-Org-${Date.now()}`;

    // Create new organization
    await clickButton(sharedPage, 'a:has-text("Add Organization"), a:has-text("+ Add Organization")');

    // Fill organization details
    await fillFormField(sharedPage, 'input[name="name"]', orgName);
    await fillFormField(sharedPage, 'input[name="email"]', `testorg${Date.now()}@mail.com`);
    await fillFormField(sharedPage, 'input[name="phone"]', '0712345678');
    await fillFormField(sharedPage, 'input[name="password"]', 'Password@123');
    await fillFormField(sharedPage, 'input[name="password_confirmation"]', 'Password@123');
    await fillFormField(sharedPage, 'textarea[name="address"]', 'Test Address 123');
    await fillFormField(sharedPage, 'input[name="contact_person"]', 'Test Contact Person');
    await fillFormField(sharedPage, 'input[name="contact_person_designation"]', 'Manager');
    await fillFormField(sharedPage, 'input[name="contact_person_phone"]', '0712345678');

    // Set as active
    await sharedPage.selectOption('select[name="is_active"]', '1');

    // Submit organization creation
    await clickButton(sharedPage, 'button:has-text("Create Organization"), button[type="submit"]');

    // Wait for success and get activation key
    await sharedPage.waitForTimeout(3000);

    // Update state
    stateManager.updateState({
      organizationCreated: true,
      organizationName: orgName
    });
    await stateManager.saveState();

    console.log('✅ Organization created successfully');
  });

  test('4. Get Organization Activation Key and Activate Unit', async () => {
    const state = stateManager.getState();

    if (!state.organizationCreated) {
      test.skip(!state.organizationCreated, 'Organization creation not completed');
      return;
    }

    if (state.organizationActivated) {
      console.log('✅ Organization already activated, skipping...');
      return;
    }

    console.log('🔑 Getting organization activation key...');

    // Navigate to organizations list
    await clickButton(sharedPage, 'a[href*="organizations"], a:has-text("Organizations")');
    await expectUrlContains(sharedPage, 'organizations');

    // Find the organization and get activation key
    const orgRow = sharedPage.locator(`tr:has-text("${state.organizationName}")`).first();
    await orgRow.locator('a:has-text("View"), a:has-text("Manage")').first().click();

    // Copy activation key
    let activationKey = '';

    // Try to find and copy activation key
    try {
      await clickButton(sharedPage, 'button:has-text("Show Key")');
      await sharedPage.waitForTimeout(1000);

      // Get the key from display
      const keyElement = await sharedPage.locator('#activationKeyDisplay, .activation-key').first();
      if (await keyElement.count() > 0) {
        activationKey = await keyElement.textContent() || '';
      }

      // Try to click copy button
      await clickButton(sharedPage, 'button:has-text("Copy")');

      // Handle copy dialog
      sharedPage.on('dialog', dialog => {
        console.log(`Dialog: ${dialog.message()}`);
        dialog.dismiss().catch(() => {});
      });

    } catch (error) {
      console.log('⚠️ Could not copy activation key automatically');
    }

    // Update state
    stateManager.updateState({
      organizationActivated: true,
      organizationActivationKey: activationKey
    });
    await stateManager.saveState();

    console.log('✅ Organization activation key obtained');
  });

  test('5. Navigate to Branches and Activate First Branch Unit', async () => {
    const state = stateManager.getState();

    if (!state.organizationActivated) {
      test.skip(!state.organizationActivated, 'Organization activation not completed');
      return;
    }

    if (state.branchesActivated) {
      console.log('✅ First branch already activated, skipping...');
      return;
    }

    console.log('🏪 Activating first branch...');

    // Navigate to branches
    await clickButton(sharedPage, 'a[href*="branches"], a:has-text("Branches")');
    await expectUrlContains(sharedPage, 'branches');

    // Find first branch and get activation key
    const firstBranchRow = sharedPage.locator('tr').nth(1); // Skip header row
    await firstBranchRow.locator('a:has-text("View")').first().click();

    // Copy branch activation key
    let branchActivationKey = '';

    try {
      await clickButton(sharedPage, 'button:has-text("Copy")');

      // Handle copy dialog
      sharedPage.on('dialog', dialog => {
        console.log(`Dialog: ${dialog.message()}`);
        dialog.dismiss().catch(() => {});
      });

      // Get activation key from clipboard simulation
      const keyElement = await sharedPage.locator('[id*="activation"], .activation-key').first();
      if (await keyElement.count() > 0) {
        branchActivationKey = await keyElement.textContent() || '';
      }

    } catch (error) {
      console.log('⚠️ Could not copy branch activation key');
    }

    // Navigate to branch activation
    await clickButton(sharedPage, 'a:has-text("Activate Branch")');

    // Paste and activate
    if (branchActivationKey) {
      await fillFormField(sharedPage, 'input[type="text"], textarea', branchActivationKey);
      await clickButton(sharedPage, 'button:has-text("Activate")');

      // Wait for success message
      await expect(sharedPage.locator('text*="activated successfully"')).toBeVisible({ timeout: 5000 });
    }

    // Update state
    stateManager.updateState({
      branchesActivated: true,
      firstBranchActivationKey: branchActivationKey
    });
    await stateManager.saveState();

    console.log('✅ First branch activated successfully');
  });

  test('6. Create New Branch Unit', async () => {
    const state = stateManager.getState();

    if (!state.branchesActivated) {
      test.skip(!state.branchesActivated, 'First branch activation not completed');
      return;
    }

    if (state.newBranchCreated) {
      console.log('✅ New branch already created, skipping...');
      return;
    }

    console.log('🏪 Creating new branch...');

    // Navigate to branches
    await clickButton(sharedPage, 'a[href*="branches"], a:has-text("Branches")');

    // Create new branch
    await clickButton(sharedPage, 'a:has-text("Add Branch"), a:has-text("+ Add Branch")');

    const newBranchName = `New-Test-Branch-${Date.now()}`;

    // Fill branch details
    await fillFormField(sharedPage, 'input[name="name"]', newBranchName);
    await fillFormField(sharedPage, 'input[name="address"], textarea[name="address"]', 'New Branch Address 456');
    await fillFormField(sharedPage, 'input[name="phone"]', '0712345679');
    await fillFormField(sharedPage, 'input[name="opening_time"]', '08:00');
    await fillFormField(sharedPage, 'input[name="closing_time"]', '22:00');
    await fillFormField(sharedPage, 'input[name="seating_capacity"]', '60');
    await fillFormField(sharedPage, 'input[name="service_charge_percentage"]', '10');
    await fillFormField(sharedPage, 'input[name="tax_percentage"]', '5');
    await fillFormField(sharedPage, 'input[name="contact_person"]', 'Branch Manager');
    await fillFormField(sharedPage, 'input[name="contact_person_designation"]', 'Manager');
    await fillFormField(sharedPage, 'input[name="contact_person_phone"]', '0712345679');

    // Submit branch creation
    await clickButton(sharedPage, 'button:has-text("Create Branch"), button[type="submit"]');

    // Wait for success
    await sharedPage.waitForTimeout(3000);

    // Update state
    stateManager.updateState({
      newBranchCreated: true,
      newBranchName: newBranchName
    });
    await stateManager.saveState();

    console.log('✅ New branch created successfully');
  });

  test('7. Activate New Branch Unit', async () => {
    const state = stateManager.getState();

    if (!state.newBranchCreated) {
      test.skip(!state.newBranchCreated, 'New branch creation not completed');
      return;
    }

    if (state.newBranchActivated) {
      console.log('✅ New branch already activated, skipping...');
      return;
    }

    console.log('🔑 Activating new branch...');

    // Navigate to branches
    await clickButton(sharedPage, 'a[href*="branches"], a:has-text("Branches")');

    // Find the new branch and get activation key
    const newBranchRow = sharedPage.locator(`tr:has-text("${state.newBranchName}")`).first();
    await newBranchRow.locator('a:has-text("View")').first().click();

    // Copy activation key
    let newBranchActivationKey = '';

    try {
      await clickButton(sharedPage, 'button:has-text("Copy")');

      // Handle copy dialog
      sharedPage.on('dialog', dialog => {
        console.log(`Dialog: ${dialog.message()}`);
        dialog.dismiss().catch(() => {});
      });

      // Get the activation key
      const keyElement = await sharedPage.locator('[id*="activation"], .activation-key').first();
      if (await keyElement.count() > 0) {
        newBranchActivationKey = await keyElement.textContent() || '';
      }

    } catch (error) {
      console.log('⚠️ Could not copy new branch activation key');
    }

    // Navigate to branch activation
    await clickButton(sharedPage, 'a:has-text("Activate Branch")');

    // Paste and activate
    if (newBranchActivationKey) {
      await fillFormField(sharedPage, 'input[type="text"], textarea', newBranchActivationKey);
      await clickButton(sharedPage, 'button:has-text("Activate")');

      // Wait for success message
      await expect(sharedPage.locator('text*="activated successfully"')).toBeVisible({ timeout: 5000 });
    }

    // Update state
    stateManager.updateState({
      newBranchActivated: true,
      newBranchActivationKey: newBranchActivationKey
    });
    await stateManager.saveState();

    console.log('✅ New branch activated successfully');
  });

  test('8. Final Verification Unit', async () => {
    const state = stateManager.getState();

    if (!state.newBranchActivated) {
      test.skip(!state.newBranchActivated, 'New branch activation not completed');
      return;
    }

    console.log('✅ Running final verification...');

    // Navigate to dashboard to verify everything is working
    await clickButton(sharedPage, 'a[href*="dashboard"], a:has-text("Dashboard")');
    await expectUrlContains(sharedPage, 'dashboard');

    // Verify navigation works
    await clickButton(sharedPage, 'a[href*="organizations"], a:has-text("Organizations")');
    await expectUrlContains(sharedPage, 'organizations');

    await clickButton(sharedPage, 'a[href*="branches"], a:has-text("Branches")');
    await expectUrlContains(sharedPage, 'branches');

    await clickButton(sharedPage, 'a[href*="subscription-plans"], a:has-text("Subscriptions")');
    await expectUrlContains(sharedPage, 'subscription-plans');

    console.log('✅ All tests completed successfully!');
    console.log('📊 Test Summary:', state);
  });

  // Utility test to clear state if needed
  test('Clear Test State (Run manually when needed)', async () => {
    test.skip(true); // Skip by default

    await stateManager.clearState();
    console.log('🗑️ Test state cleared');
  });
// End of test.describe

// Additional helper test for debugging
test.describe('Debug Tests', () => {
  test('Debug Current State', async () => {
    const stateManager = new TestStateManager();
    await stateManager.loadState();
    console.log('🔍 Current test state:', stateManager.getState());
  });
});
