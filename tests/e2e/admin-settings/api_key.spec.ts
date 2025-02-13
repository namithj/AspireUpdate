import { expect, test } from '@playwright/test';
import { fieldSelector, enable, api_key } from '../data/fields';
import { settings } from '../data/routes';

test.describe('[Admin Settings] [Field] API Key', () => {
	test.beforeEach(async ( { page }) => {
		const baseURL = test.info().project.use.baseURL;
		const settingsPage = `${baseURL}/wp-admin/${settings}`;
		await page.goto(settingsPage);
		await page.waitForURL(settingsPage);
	});

	test('is invisible by default', async ({ page }) => {
		await expect(page.locator(fieldSelector + api_key)).not.toBeVisible();
	});

	test('is visible when API rewriting is enabled', async ({ page }) => {
		await page.check(fieldSelector + enable);
		await expect(page.locator(fieldSelector + api_key)).toBeVisible();
	});

	test('is empty by default', async ({ page }) => {
		await page.check(fieldSelector + enable);
		await expect(page.locator(fieldSelector + api_key)).toBeVisible();
		await expect(page.locator(fieldSelector + api_key)).toHaveValue('');
	});
})
