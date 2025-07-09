import { test, expect } from '@playwright/test';
import { loginAsSuperAdmin } from './helpers/loginAsSuperAdmin'
import { createSubscriptionPlan } from './helpers/createSubscriptionPlan';
import { createOrganization } from './helpers/createOrganization';
import { activateBranch } from './helpers/activateBranch';

test('admin workflow', async ({ page }) => {
  await loginAsSuperAdmin(page);
  await createSubscriptionPlan(page);
  await createOrganization(page);
  // await activateBranch(page);
  // ...continue splitting and calling other helpers for user, supplier, inventory, etc.
});
