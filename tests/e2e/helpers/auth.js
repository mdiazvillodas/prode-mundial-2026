import { expect } from '@playwright/test';

export const demoUsers = {
    admin: {
        email: 'admin@prode.test',
        password: 'password',
    },
    mariano: {
        email: 'mariano@prode.test',
        password: 'password',
    },
};

export async function login(page, user = demoUsers.mariano) {
    await page.goto('/login');
    await page.getByLabel('Email').fill(user.email);
    await page.getByLabel(/Contrase/).fill(user.password);
    await page.getByRole('button', { name: /Iniciar sesi.n/i }).click();

    await expect(page.getByRole('navigation')).toContainText('Predicciones');
    await handleAvatarPrompt(page);
}

export async function expectAppShell(page) {
    await expect(page.getByRole('navigation')).toContainText('Inicio');
    await expect(page.getByRole('navigation')).toContainText('Predicciones');
    await expect(page.getByRole('navigation')).toContainText('Ligas');
}

export async function handleAvatarPrompt(page) {
    const promptHeading = page.getByRole('heading', { name: /Elegí tu avatar|Elegi tu avatar/i });

    if (!await promptHeading.isVisible().catch(() => false)) {
        return;
    }

    await page
        .locator('input[name="profile_avatar_key"][value="default"]')
        .check({ force: true });

    await page.getByRole('button', { name: /Guardar y continuar/i }).click();
    await expect(promptHeading).toBeHidden();
}