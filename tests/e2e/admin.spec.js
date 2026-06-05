import { expect, test } from '@playwright/test';
import { demoUsers, login } from './helpers/auth.js';

test('admin smoke loads dashboard and matches listing', async ({ page }) => {
    await login(page, demoUsers.admin);

    await page.goto('/admin');
    await expect(page.getByRole('heading', { name: 'Administracion' })).toBeVisible();
    await expect(page.getByText('Entorno', { exact: true })).toBeVisible();
    await expect(page.getByText('Modo', { exact: true })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Admin partidos' }).first()).toBeVisible();

    await page.goto('/admin/matches');
    await expect(page.getByRole('heading', { name: 'Admin partidos' })).toBeVisible();
    await expect(page.getByText(/Listado minimo|Todavia no hay partidos|Fase/).first()).toBeVisible();
});
