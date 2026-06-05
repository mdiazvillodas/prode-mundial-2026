import { expect, test } from '@playwright/test';
import { demoUsers, login } from './helpers/auth.js';

test('prediction pre-results smoke loads and saves an editable prediction when available', async ({ page }) => {
    await login(page, demoUsers.mariano);

    await page.goto('/predictions');
    await expect(page.getByRole('heading', { name: 'Predicciones' })).toBeVisible();
    await expect(page.getByText(/Jornada|Todav/i).first()).toBeVisible();

    const scoreInputs = page.locator('[data-prediction-input]').and(page.locator('input[type="number"]'));
    const editableScores = await scoreInputs.count();

    if (editableScores === 0) {
        throw new Error('No editable prediction inputs were found. Run php artisan demo:reset-staging --force before pre-results E2E smoke.');
    }

    await scoreInputs.nth(0).fill('2');
    await scoreInputs.nth(1).fill('1');

    await expect(page.locator('#floating-save')).toBeVisible();
    await page.getByRole('button', { name: 'Guardar cambios' }).click();
    await expect(page.getByRole('status')).toContainText(/Predicciones guardadas|Prediccion guardada/);
});
