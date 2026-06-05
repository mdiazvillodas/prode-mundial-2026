import { expect, test } from '@playwright/test';
import { demoUsers, login } from './helpers/auth.js';

test('history smoke shows pending or scored prediction states', async ({ page }) => {
    await login(page, demoUsers.mariano);

    await page.goto('/my-predictions');
    await expect(page.getByRole('heading', { name: 'Mis predicciones' })).toBeVisible();
    await expect(page.getByText('Tu prediccion').first()).toBeVisible();
    await expect(page.getByText('Resultado').first()).toBeVisible();
    await expect(page.getByText('Puntos').first()).toBeVisible();
    await expect(page.getByText(/Pendiente|Puntuada|Cerrada/).first()).toBeVisible();
});
