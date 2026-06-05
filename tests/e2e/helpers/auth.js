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
    await page.getByRole('button', { name: /Iniciar sesion/i }).click();

    await expect(page.getByRole('navigation')).toContainText('Predicciones');
}

export async function expectAppShell(page) {
    await expect(page.getByRole('navigation')).toContainText('Inicio');
    await expect(page.getByRole('navigation')).toContainText('Predicciones');
    await expect(page.getByRole('navigation')).toContainText('Ligas');
}
