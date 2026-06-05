import { expect, test } from '@playwright/test';
import { demoUsers, login } from './helpers/auth.js';

test('leagues smoke shows general league and private league ranking', async ({ page }) => {
    await login(page, demoUsers.mariano);

    await page.goto('/leagues');
    await expect(page.getByRole('heading', { name: 'Ligas' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Liga general' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Tabla de posiciones' })).toBeVisible();
    await expect(page.getByText('Puntos').first()).toBeVisible();

    const privateLeagueTab = page.getByRole('button', { name: /Liga Demo Palermo/ });

    if (await privateLeagueTab.isVisible().catch(() => false)) {
        await privateLeagueTab.click();
        await expect(page.getByText('Liga privada').first()).toBeVisible();
        await expect(page.getByText('Tabla de posiciones de la liga', { exact: true })).toBeVisible();
    }
});
