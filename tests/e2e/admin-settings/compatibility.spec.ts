import { expect, test } from '@playwright/test';
import { fieldWrapperSelector, fieldSelector, enable, compatibility } from '../data/fields';
import { settings } from '../data/routes';

test.describe('[Admin Settings] [Field] Compatibility', () => {
	test.beforeEach(async ({ page }) => {
		const baseURL = test.info().project.use.baseURL;
		const settingsPage = `${baseURL}/wp-admin/${settings}`;
		await page.goto(settingsPage);
		await page.waitForURL(settingsPage);
	});

	test('is invisible by default', async ({ page }) => {
		await expect(page.locator(fieldWrapperSelector + compatibility)).not.toBeVisible();
	});

	test('is visible when API rewriting is enabled', async ({ page }) => {
		await page.locator(fieldSelector + enable).check();
		await expect(page.locator(fieldWrapperSelector + compatibility)).toBeVisible();
	});
})
