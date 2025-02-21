import { expect, test } from '@playwright/test';
import { fieldSelector, enable, api_host } from '../data/fields';
import { settings } from '../data/routes';

test.describe('[Admin Settings] [Field] API Host', () => {
	test.beforeEach(async ( { page }) => {
		const baseURL = test.info().project.use.baseURL;
		const settingsPage = `${baseURL}/wp-admin/${settings}`;
		await page.goto(settingsPage);
		await page.waitForURL(settingsPage);
	});

	test('is not visible by default', async ({ page }) => {
		await expect(page.locator(fieldSelector + api_host)).not.toBeVisible();
	});

	test('is visible when API rewriting is enabled', async ({ page }) => {
		await page.locator(fieldSelector + enable).check();
		await expect(page.locator(fieldSelector + api_host)).toBeVisible();
	});

	test('has three options', async ({ page }) => {
		await page.check(fieldSelector + enable);
		await expect(page.locator(fieldSelector + api_host)).toBeVisible();
		await expect(page.locator(fieldSelector + api_host + ' option')).toHaveCount(3);
	});

	test('is AspireCloud by default', async ({ page }) => {
		await page.check(fieldSelector + enable);
		await expect(page.locator(fieldSelector + api_host)).toBeVisible();
		await expect(page.locator(fieldSelector + api_host)).toHaveValue('https://api.aspirecloud.net');
	});

	test('has a bleeding edge option', async ({ page }) => {
		await page.check(fieldSelector + enable);
		await expect(page.locator(fieldSelector + api_host)).toBeVisible();

		const options = await page.locator(fieldSelector + api_host + ' option').all();
		await expect(await options[1].getAttribute('value')).toBe('https://api.aspirecloud.io');
	});

	test('has an Other option', async ({ page }) => {
		await page.check(fieldSelector + enable);
		await expect(page.locator(fieldSelector + api_host)).toBeVisible();

		const options = await page.locator(fieldSelector + api_host + ' option').all();
		await expect(await options[2].getAttribute('value')).toBe('other');
	});
})
