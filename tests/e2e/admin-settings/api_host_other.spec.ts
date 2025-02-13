import { expect, test } from '@playwright/test';
import { fieldSelector, enable, api_host, api_host_other } from '../data/fields';
import { settings } from '../data/routes';

test.describe('[Admin Settings] [Field] API Host Other', () => {
	test.beforeEach(async ( { page }) => {
		const baseURL = test.info().project.use.baseURL;
		const settingsPage = `${baseURL}/wp-admin/${settings}`;
		await page.goto(settingsPage);
		await page.waitForURL(settingsPage);
	});

	test('is not visible by default', async ({ page }) => {
		await expect(page.locator(fieldSelector + api_host_other)).not.toBeVisible();
	});

	test('is not visible when API rewriting is enabled', async ({ page }) => {
		await page.check(fieldSelector + enable);
		await expect(page.locator(fieldSelector + api_host_other)).not.toBeVisible();
	});

	test('is visible when API Host is set to Other', async ({ page }) => {
		await page.check(fieldSelector + enable);
		await page.selectOption(fieldSelector + api_host, 'other' );
		await expect(page.locator(fieldSelector + api_host_other)).toBeVisible();
	});

	test('is empty by default', async ({ page }) => {
		await page.check(fieldSelector + enable);
		await page.selectOption(fieldSelector + api_host, 'other' );
		await expect(page.locator(fieldSelector + api_host_other)).toBeVisible();
		await expect(page.locator(fieldSelector + api_host_other)).toHaveValue('');
	});
})
