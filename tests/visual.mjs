import { chromium } from 'playwright-core';
import { mkdir } from 'node:fs/promises';

const browser = await chromium.launch({
  executablePath: process.env.CHROME_BIN,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});
const page = await browser.newPage({ viewport: { width: 1100, height: 850 }, deviceScaleFactor: 1 });
const base = 'http://127.0.0.1:8766';
await mkdir('visual-captures', { recursive: true });

async function login(email) {
  await page.goto(`${base}/login`);
  await page.getByLabel('Adresse email').fill(email);
  await page.getByLabel('Mot de passe').fill(process.env.VISUAL_PASS);
  await page.getByRole('button', { name: 'Se connecter' }).click();
}

try {
  await page.goto(base);
  await page.getByRole('heading', { name: /Pour obtenir plus d’informations/ }).waitFor();
  await page.screenshot({ path: 'visual-captures/01-visiteur.png' });

  await login('visual@example.test');
  await page.getByRole('heading', { name: 'Trajets proposés' }).waitFor();
  await page.screenshot({ path: 'visual-captures/02-employe.png' });
  await page.getByRole('button', { name: 'Détails' }).first().click();
  await page.getByRole('heading', { name: 'Détails du trajet' }).waitFor();
  await page.screenshot({ path: 'visual-captures/03-details.png' });
  await page.getByRole('button', { name: 'Fermer' }).last().click();

  await page.getByRole('link', { name: 'Créer un trajet' }).click();
  await page.locator('#departure').selectOption({ label: 'Paris' });
  await page.locator('#arrival').selectOption({ label: 'Lyon' });
  const departure = new Date(Date.now() + 8 * 86400000);
  const arrival = new Date(departure.getTime() + 2 * 3600000);
  const local = (d) => new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
  await page.locator('#departure_at').fill(local(departure));
  await page.locator('#arrival_at').fill(local(arrival));
  await page.getByRole('button', { name: 'Enregistrer' }).click();
  await page.getByRole('status').waitFor();
  await page.screenshot({ path: 'visual-captures/04-message-flash.png' });

  await page.getByRole('button', { name: 'Déconnexion' }).click();
  await login('visualadmin@example.test');
  await page.getByRole('heading', { name: 'Tableau de bord administrateur' }).waitFor();
  await page.screenshot({ path: 'visual-captures/05-administrateur.png' });
} finally {
  await browser.close();
}
