import { Page } from '@playwright/test';

export async function activateBranch(page: Page) {
  console.log('Navigating to branch summary page');
  await page.goto('https://rms.test/admin/branches/1/summary');
  const testRunId = process.env.TEST_RUN_ID || 'default';
  await page.screenshot({ path: `test-results/screen-shots/${testRunId}/activate-branch-step-1-summary.png` });

  console.log('Filling login credentials');
  await page.getByRole('textbox', { name: 'Email' }).fill('superadmin@rms.com');
  await page.getByRole('textbox', { name: 'Password' }).fill('SuperAdmin123!');
  await page.screenshot({ path: `test-results/screen-shots/${testRunId}/activate-branch-step-2-login-filled.png` });
  await page.getByRole('button', { name: 'Login' }).click();

  // Handle dialog and click "Copy"
  page.once('dialog', async dialog => {
    console.log('Dialog appeared, dismissing');
    await dialog.dismiss();
  });
  console.log('Clicking Copy to get activation key');
  await page.getByRole('button', { name: 'Copy' }).click();
  await page.screenshot({ path: `test-results/screen-shots/${testRunId}/activate-branch-step-3-after-copy.png` });

  // Wait for the activation key textbox to be visible and get its value
  const keyTextbox = await page.getByRole('textbox').first();
  await keyTextbox.waitFor({ state: 'visible' });
  const activationKey = (await keyTextbox.inputValue()).trim();
  console.log('Activation key obtained:', activationKey);

  // Go to activation page and use the key
  console.log('Navigating to activation page');
  await page.goto('https://rms.test/admin/branches/activate');
  await page.screenshot({ path: `test-results/screen-shots/${testRunId}/activate-branch-step-4-activation-page.png` });
  await page.getByRole('textbox').fill(activationKey);
  await page.screenshot({ path: `test-results/screen-shots/${testRunId}/activate-branch-step-5-key-filled.png` });
  console.log('Clicking Activate');
  await page.getByRole('button', { name: 'Activate' }).click();

  // Wait for success or error
  try {
    await page.waitForSelector('.alert-success, .alert-error', { timeout: 10000 });
    await page.screenshot({ path: `test-results/screen-shots/${testRunId}/activate-branch-step-6-result.png` });
    console.log('Activation result displayed');
  } catch (e) {
    console.warn('No result message appeared after activation');
    await page.screenshot({ path: `test-results/screen-shots/${testRunId}/activate-branch-step-6-no-result.png` });
  }
}
