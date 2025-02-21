import { expect, test } from '@playwright/test';
import { fieldSelector, enable_debug, disable_ssl } from '../data/fields';
import { settings } from '../data/routes';

test.describe('[Admin Settings] [Field] Disable SSL Verification', () => {
	test.beforeEach(async ({ page }) => {
		const baseURL = test.info().project.use.baseURL;
		const settingsPage = `${baseURL}/wp-admin/${settings}`;
		await page.goto(settingsPage);
		await page.waitForURL(settingsPage);
	});

	test('is invisible by default', async ({ page }) => {
		await expect(page.locator(fieldSelector + disable_ssl)).not.toBeVisible();
	});

	test('is visible when debugging is enabled', async ({ page }) => {
		await page.check(fieldSelector + enable_debug);
		await expect(page.locator(fieldSelector + disable_ssl)).toBeVisible();
	});

	test('is unchecked by default', async ({ page }) => {
		await page.check(fieldSelector + enable_debug);
		await expect(page.locator(fieldSelector + disable_ssl)).not.toBeChecked();
	});
})
