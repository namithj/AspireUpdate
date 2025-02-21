import { expect, test } from '@playwright/test';
import { fieldSelector, enable_debug, debug_response } from '../data/fields';
import { settings } from '../data/routes';

test.describe('[Admin Settings] [Field] Debug Type: Response', () => {
	test.beforeEach(async ({ page }) => {
		const baseURL = test.info().project.use.baseURL;
		const settingsPage = `${baseURL}/wp-admin/${settings}`;
		await page.goto(settingsPage);
		await page.waitForURL(settingsPage);
	});

	test('is invisible by default', async ({ page }) => {
		await expect(page.locator(fieldSelector + debug_response)).not.toBeVisible();
	});

	test('is visible when debugging is enabled', async ({ page }) => {
		await page.check(fieldSelector + enable_debug);
		await expect(page.locator(fieldSelector + debug_response)).toBeVisible();
	});

	test('is unchecked by default', async ({ page }) => {
		await page.check(fieldSelector + enable_debug);
		await expect(page.locator(fieldSelector + debug_response)).not.toBeChecked();
	});
})
