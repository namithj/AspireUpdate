import { expect, test } from '@playwright/test';
import { fieldSelector, enable } from '../data/fields';
import { settings } from '../data/routes';

test.describe('[Admin Settings] [Field] Enable', () => {
	test.beforeEach(async ({ page }) => {
		const baseURL = test.info().project.use.baseURL;
		const settingsPage = `${baseURL}/wp-admin/${settings}`;
		await page.goto(settingsPage);
		await page.waitForURL(settingsPage);
	});

	test('is visible by default', async ({ page }) => {
		await expect(page.locator(fieldSelector + enable)).toBeVisible();
	});

	test('is unchecked by default', async ({ page }) => {
		await expect(page.locator(fieldSelector + enable)).not.toBeChecked();
	});
})
