import { expect, test } from '@playwright/test';
import { demoUsers, login } from './helpers/auth.js';

test('authenticated navigation smoke reaches dashboard calendar and leaderboard', async ({ page }) => {
    await login(page, demoUsers.mariano);

    await page.goto('/dashboard');
    await expect(page.getByRole('navigation')).toContainText('Predicciones');
    await expect(page.getByText(/Predicciones|Ligas|Historial/).first()).toBeVisible();

    await page.goto('/calendar');
    await expect(page.getByRole('heading', { name: 'Calendario' })).toBeVisible();
    await expect(page.locator('[data-team-selector]')).toBeVisible();

    await page.goto('/leaderboard');
    await expect(page.getByRole('heading', { name: 'Liga general' })).toBeVisible();
    await expect(page.getByText(/Tabla de posiciones|Puntos/).first()).toBeVisible();
});
