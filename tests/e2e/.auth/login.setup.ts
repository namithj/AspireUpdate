import test, { test as setup } from '@playwright/test';
import path from 'path';

const authFile = path.join(__dirname, '../artifacts/.auth/user.json');

setup('login', async ({ page }) => {
	const loginPage = `${test.info().project.use.baseURL}/wp-login.php`;
	await page.goto(loginPage);
	await page.waitForURL(loginPage);

	await page.fill('#user_login', 'admin');
	await page.fill('#user_pass', 'password');

	await page.waitForTimeout(1000);
	await page.click('#wp-submit');

	await page.context().storageState({ path: authFile });
});
