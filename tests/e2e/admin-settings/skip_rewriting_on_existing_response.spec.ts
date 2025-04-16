import { expect, test } from '@playwright/test';
import { fieldSelector, enable, skip_rewriting_on_existing_response } from '../data/fields';
import { settings } from '../data/routes';

test.describe('[Admin Settings] [Field] Compatibility: Skip API Rewriting', () => {
	test.beforeEach(async ({ page }) => {
		const baseURL = test.info().project.use.baseURL;
		const settingsPage = `${baseURL}/wp-admin/${settings}`;
		await page.goto(settingsPage);
		await page.waitForURL(settingsPage);
	});

	test('is not visible by default', async ({ page }) => {
		await expect(page.locator(fieldSelector + skip_rewriting_on_existing_response)).not.toBeVisible();
	});

	test('is visible when API rewriting is enabled', async ({ page }) => {
		await page.locator(fieldSelector + enable).check();
		await expect(page.locator(fieldSelector + skip_rewriting_on_existing_response)).toBeVisible();
	});
})
