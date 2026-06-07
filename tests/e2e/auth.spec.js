import { expect, test } from '@playwright/test';
import { demoUsers, expectAppShell, login } from './helpers/auth.js';

test('auth smoke redirects guests and logs in demo user', async ({ page }) => {
    await page.goto('/login');
    await expect(page.getByLabel('Email')).toBeVisible();
    await expect(page.getByRole('button', { name: /Iniciar sesi.n/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /Olvid/i })).toBeVisible();

    await page.goto('/register');
    await expect(page.getByRole('heading', { name: /Crear cuenta/ })).toBeVisible();
    await expect(page.getByLabel('Email')).toBeVisible();

    await page.goto('/forgot-password');
    await expect(page.getByRole('heading', { name: /Olvidé mi contraseña/ })).toBeVisible();
    await expect(page.getByLabel('Email')).toBeVisible();

    await page.goto('/predictions');
    await expect(page).toHaveURL(/\/login$/);
    await expect(page.getByLabel('Email')).toBeVisible();

    await login(page, demoUsers.mariano);
    await expectAppShell(page);
    await page.goto('/dashboard');
    await expectAppShell(page);
    await expect(page.getByRole('link', { name: 'Predicciones' }).first()).toBeVisible();
});
