import { expect, test } from '@playwright/test';
import { demoUsers, login } from './helpers/auth.js';

test('admin smoke loads dashboard and matches listing', async ({ page }) => {
    await login(page, demoUsers.admin);

    await page.goto('/admin');
    await expect(page.getByRole('heading', { name: 'Administracion' })).toBeVisible();
    await expect(page.getByText('Entorno', { exact: true })).toBeVisible();
    await expect(page.getByText('Modo', { exact: true })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Admin partidos' }).first()).toBeVisible();
    await expect(page.getByRole('link', { name: 'Usuarios / Emails' }).first()).toBeVisible();

    await page.goto('/admin/matches');
    await expect(page.getByRole('heading', { name: 'Admin partidos' })).toBeVisible();
    await expect(page.getByText(/Listado minimo|Todavia no hay partidos|Fase/).first()).toBeVisible();

    await page.goto('/admin/api-health');
    await expect(page.getByRole('heading', { name: 'Estado API-Football' })).toBeVisible();
    await expect(page.getByText('Logs recientes')).toBeVisible();
    await expect(page.getByText('Equipos API en DB')).toBeVisible();
    await expect(page.getByText('Fixtures API en DB')).toBeVisible();

    await page.goto('/admin/users');
    await expect(page.getByRole('heading', { name: 'Admin usuarios' })).toBeVisible();
    await expect(page.getByRole('columnheader', { name: 'Verificación' })).toBeVisible();
    await expect(page.getByRole('cell', { name: demoUsers.admin.email })).toBeVisible();
});

test('normal user cannot access admin API health', async ({ page }) => {
    await login(page, demoUsers.mariano);

    await page.goto('/admin/api-health');
    await expect(page.getByText(/403|Forbidden|Esta acción no está autorizada|This action is unauthorized/i).first()).toBeVisible();
});
