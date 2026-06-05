import { expect, test } from '@playwright/test';
import { demoUsers, expectAppShell, login } from './helpers/auth.js';

test('auth smoke redirects guests and logs in demo user', async ({ page }) => {
    await page.goto('/predictions');
    await expect(page).toHaveURL(/\/login$/);
    await expect(page.getByLabel('Email')).toBeVisible();

    await login(page, demoUsers.mariano);
    await expectAppShell(page);
    await expect(page.getByRole('link', { name: 'Predicciones' }).first()).toBeVisible();
});
