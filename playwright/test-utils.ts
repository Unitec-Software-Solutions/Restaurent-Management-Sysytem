import { test, expect, Page } from '@playwright/test';

/**
 * Enhanced Test Utilities for Restaurant Management System
 * Provides robust key extraction and clipboard simulation
 */

export class TestUtils {
  private page: Page;

  constructor(page: Page) {
    this.page = page;
  }

  /**
   * Enhanced activation key extractor with multiple strategies
   */
  async extractActivationKey(): Promise<string> {
    let activationKey = '';

    // Strategy 1: Try to find visible key display elements
    const keySelectors = [
      '#activationKeyDisplay',
      '.activation-key',
      '[data-activation-key]',
      'input[readonly][value]',
      'code',
      '.font-mono',
      '.bg-gray-100'
    ];

    for (const selector of keySelectors) {
      try {
        const element = this.page.locator(selector);
        if (await element.count() > 0) {
          const text = await element.textContent();
          if (text && text.length > 30 && /^[a-zA-Z0-9]+$/.test(text)) {
            activationKey = text.trim();
            console.log(`✅ Found key using selector: ${selector}`);
            break;
          }
        }
      } catch (error) {
        // Continue to next strategy
      }
    }

    // Strategy 2: Look for input fields with long values
    if (!activationKey) {
      try {
        const inputs = await this.page.locator('input[type="text"], input[type="password"]').all();
        for (const input of inputs) {
          const value = await input.getAttribute('value');
          if (value && value.length > 30 && /^[a-zA-Z0-9]+$/.test(value)) {
            activationKey = value;
            console.log('✅ Found key in input field');
            break;
          }
        }
      } catch (error) {
        // Continue to next strategy
      }
    }

    // Strategy 3: Check for keys in table cells or divs
    if (!activationKey) {
      try {
        const textElements = await this.page.locator('td, div, span').all();
        for (const element of textElements) {
          const text = await element.textContent();
          if (text && text.length > 30 && /^[a-zA-Z0-9]+$/.test(text.trim())) {
            activationKey = text.trim();
            console.log('✅ Found key in text element');
            break;
          }
        }
      } catch (error) {
        // Continue
      }
    }

    return activationKey;
  }

  /**
   * Enhanced copy button interaction with fallback strategies
   */
  async copyActivationKey(): Promise<string> {
    let activationKey = '';

    // Try to show key first
    try {
      const showButtons = ['Show Key', 'Show', 'Reveal'];
      for (const buttonText of showButtons) {
        const button = this.page.locator(`button:has-text("${buttonText}")`);
        if (await button.count() > 0) {
          await button.click();
          await this.page.waitForTimeout(1000);
          break;
        }
      }
    } catch (error) {
      console.log('⚠️ Could not find show key button');
    }

    // Extract key using enhanced method
    activationKey = await this.extractActivationKey();

    // Try copy button as secondary action
    try {
      const copyButton = this.page.locator('button:has-text("Copy")');
      if (await copyButton.count() > 0) {
        await copyButton.click();

        // Handle any dialog
        this.page.on('dialog', dialog => {
          console.log(`Dialog: ${dialog.message()}`);
          dialog.dismiss().catch(() => {});
        });
      }
    } catch (error) {
      console.log('⚠️ Could not click copy button');
    }

    return activationKey;
  }

  /**
   * Navigate to specific section with retries
   */
  async navigateToSection(sectionName: string): Promise<void> {
    const navigationMap: { [key: string]: string[] } = {
      'dashboard': ['Dashboard', 'dashboard'],
      'organizations': ['Organizations', 'organizations'],
      'branches': ['Branches', 'branches'],
      'subscription-plans': ['Subscriptions', 'Subscription Plans', 'subscription-plans'],
      'modules': ['Modules', 'modules'],
      'users': ['Users', 'User Management', 'users']
    };

    const possibleSelectors = navigationMap[sectionName] || [sectionName];

    for (const selector of possibleSelectors) {
      try {
        // Try different navigation patterns
        const navSelectors = [
          `a[href*="${selector}"]`,
          `a:has-text("${selector}")`,
          `button:has-text("${selector}")`,
          `.nav-item:has-text("${selector}")`,
          `.sidebar a:has-text("${selector}")`
        ];

        for (const navSelector of navSelectors) {
          const element = this.page.locator(navSelector);
          if (await element.count() > 0) {
            await element.first().click();
            await this.page.waitForTimeout(2000);
            return;
          }
        }
      } catch (error) {
        continue;
      }
    }

    throw new Error(`Could not navigate to section: ${sectionName}`);
  }

  /**
   * Wait for successful page load with multiple indicators
   */
  async waitForPageLoad(expectedUrlPart?: string): Promise<void> {
    // Wait for network to be idle
    await this.page.waitForLoadState('networkidle');

    // Wait for common loading indicators to disappear
    const loadingSelectors = [
      '.loading',
      '.spinner',
      '[data-loading]',
      '.fa-spinner'
    ];

    for (const selector of loadingSelectors) {
      try {
        await this.page.waitForSelector(selector, { state: 'hidden', timeout: 5000 });
      } catch (error) {
        // Continue if loading indicator not found
      }
    }

    // Check URL if expected
    if (expectedUrlPart) {
      await expect(this.page).toHaveURL(new RegExp(expectedUrlPart));
    }
  }

  /**
   * Enhanced form filling with validation
   */
  async fillFormFieldSafely(selector: string, value: string): Promise<void> {
    await this.page.waitForSelector(selector, { timeout: 10000 });

    // Clear existing value
    await this.page.fill(selector, '');
    await this.page.waitForTimeout(500);

    // Fill new value
    await this.page.fill(selector, value);

    // Verify the value was set
    const filledValue = await this.page.locator(selector).inputValue();
    if (filledValue !== value) {
      throw new Error(`Failed to fill field ${selector}. Expected: ${value}, Got: ${filledValue}`);
    }
  }

  /**
   * Smart button clicking with multiple strategies
   */
  async clickButtonSafely(buttonText: string): Promise<void> {
    const buttonSelectors = [
      `button:has-text("${buttonText}")`,
      `input[type="submit"][value="${buttonText}"]`,
      `a:has-text("${buttonText}")`,
      `[role="button"]:has-text("${buttonText}")`,
      `.btn:has-text("${buttonText}")`
    ];

    for (const selector of buttonSelectors) {
      try {
        const element = this.page.locator(selector);
        if (await element.count() > 0) {
          await element.first().click();
          return;
        }
      } catch (error) {
        continue;
      }
    }

    throw new Error(`Could not find button with text: ${buttonText}`);
  }

  /**
   * Check if element exists and is visible
   */
  async isElementVisible(selector: string): Promise<boolean> {
    try {
      const element = this.page.locator(selector);
      return await element.count() > 0 && await element.first().isVisible();
    } catch (error) {
      return false;
    }
  }

  /**
   * Wait for success message to appear
   */
  async waitForSuccessMessage(timeout: number = 10000): Promise<boolean> {
    const successSelectors = [
      '.alert-success',
      '.success',
      'text*="success"',
      'text*="Success"',
      'text*="created successfully"',
      'text*="activated successfully"',
      '.text-green',
      '.bg-green'
    ];

    for (const selector of successSelectors) {
      try {
        await this.page.waitForSelector(selector, { timeout });
        return true;
      } catch (error) {
        continue;
      }
    }

    return false;
  }

  /**
   * Find and click row action button by row identifier
   */
  async clickRowAction(rowIdentifier: string, actionText: string): Promise<void> {
    const row = this.page.locator(`tr:has-text("${rowIdentifier}")`).first();
    const actionButton = row.locator(`a:has-text("${actionText}"), button:has-text("${actionText}")`);

    if (await actionButton.count() > 0) {
      await actionButton.first().click();
    } else {
      throw new Error(`Could not find action "${actionText}" for row containing "${rowIdentifier}"`);
    }
  }

  /**
   * Screenshot for debugging
   */
  async takeDebugScreenshot(name: string): Promise<void> {
    await this.page.screenshot({ path: `debug-${name}-${Date.now()}.png` });
  }
}

/**
 * Test data generators
 */
export class TestDataGenerator {
  static generateOrganizationName(): string {
    return `Test-Org-${Date.now()}-${Math.random().toString(36).substr(2, 5)}`;
  }

  static generateBranchName(): string {
    return `Test-Branch-${Date.now()}-${Math.random().toString(36).substr(2, 5)}`;
  }

  static generatePlanName(): string {
    return `Test-Plan-${Date.now()}-${Math.random().toString(36).substr(2, 5)}`;
  }

  static generateEmail(): string {
    return `test${Date.now()}@example.com`;
  }

  static getTestPhoneNumber(): string {
    return '0712345678';
  }

  static getTestPassword(): string {
    return 'Password@123';
  }
}

/**
 * Enhanced state validator
 */
export class StateValidator {
  static validateState(state: any, requiredFields: string[]): boolean {
    return requiredFields.every(field => {
      const value = state[field];
      return value !== undefined && value !== null && value !== '';
    });
  }

  static getStateCompletionPercentage(state: any): number {
    const totalFields = Object.keys(state).length;
    const completedFields = Object.values(state).filter(value =>
      value !== undefined && value !== null && value !== false && value !== ''
    ).length;

    return Math.round((completedFields / totalFields) * 100);
  }
}
