import { test, expect } from '@playwright/test';
import { loginasorganizationadmin } from './helpers/login_org_admin';
import { createSubscriptionPlan } from './helpers/createSubscriptionPlan';
import { createOrganization } from './helpers/createOrganization';
import { activateBranch } from './helpers/activateBranch';
import { createSupplier } from './helpers/createSupplier';

test('ORG Admin Workflow 🚀', async ({ page }) => {
    await loginasorganizationadmin(page);
});




