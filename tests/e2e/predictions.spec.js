import { expect, test } from '@playwright/test';
import { demoUsers, login } from './helpers/auth.js';

test.use({ timezoneId: 'Europe/Madrid' });

test('prediction pre-results smoke loads and saves an editable prediction when available', async ({ page }) => {
    await login(page, demoUsers.mariano);

    await page.goto('/predictions');
    await expect(page.getByRole('heading', { name: 'Predicciones' })).toBeVisible();
    await expect(page.locator('[data-date-nav]')).toBeVisible();
    await expect(page.locator('[data-active-date-chip]')).toBeVisible();

    const dateChips = page.locator('[data-date-chip]');
    const dateChipCount = await dateChips.count();

    if (dateChipCount === 0) {
        throw new Error('No prediction date chips were found. Run php artisan demo:reset-staging --force before E2E smoke.');
    }

    let savedPrediction = false;
    let anyEditableDate = false;

    for (let index = 0; index < dateChipCount; index += 1) {
        const chip = dateChips.nth(index);
        const isActive = await chip.getAttribute('data-active-date-chip') !== null;

        if (!isActive) {
            await Promise.all([
                page.waitForNavigation({ url: /\/predictions/, waitUntil: 'networkidle' }),
                chip.click(),
            ]);
        }

        await expect(page.locator('[data-date-nav]')).toBeVisible();
        await expect(page.locator('[data-active-date-chip]')).toBeVisible();

        const predictionInputs = page.locator('[data-prediction-input]:visible');
        const editableCount = await predictionInputs.count();

        if (editableCount === 0) {
            continue;
        }

        const numberInputs = page.locator('input[data-prediction-input]:visible');
        const numberCount = await numberInputs.count();

        if (numberCount < 2) {
            continue;
        }

        anyEditableDate = true;
        await numberInputs.nth(0).fill('2');
        await numberInputs.nth(1).fill('1');

        const selectInputs = page.locator('select[data-prediction-input]:visible');
        if (await selectInputs.count() > 0) {
            await selectInputs.nth(0).selectOption({ index: 1 });
        }

        await expect(page.locator('#floating-save')).toBeVisible();
        await page.locator('#floating-save-button').click();
        await expect(page.getByRole('status')).toContainText(/Predicciones guardadas|Prediccion guardada/);

        savedPrediction = true;
        break;
    }

    if (!savedPrediction) {
        if (!anyEditableDate) {
            throw new Error('No editable prediction inputs were found on any available date chip. Ensure demo staging data includes at least one open future match.');
        }

        throw new Error('An editable prediction date was found, but the page did not expose enough editable score inputs to save a prediction.');
    }
});
