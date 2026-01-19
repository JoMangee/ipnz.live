import { test, expect } from '@playwright/test';

// Verify join form preserves data on validation error and sets referral on success
// v2.0: Tests updated for alphanumeric referral codes (6-char format, e.g., GEWEEN)

test('join form preserves inputs on invalid email', async ({ page }) => {
  await page.goto('/join.php');
  // Wait longer for page to fully load and render form
  await page.waitForTimeout(3000);
  
  // Debug: Check what's on the page if form not found
  const formExists = await page.locator('#join-form-name').count();
  if (formExists === 0) {
    console.log('Form not found. Page title:', await page.title());
    console.log('Page URL:', page.url());
    console.log('Body text (first 500 chars):', (await page.textContent('body'))?.substring(0, 500));
  }
  
  await expect(page.locator('#join-form-name')).toBeVisible({ timeout: 15000 });

  await page.fill('#join-form-name', 'Test User');
  await page.fill('#join-form-email', 'invalid-email');
  await page.fill('input[name="join-form-phone"]', '028-25578835');
  await page.fill('#join-form-message', 'Hello');

  await page.click('button[type="submit"]');
  
  // Wait for form submission to process and error alert to appear
  await page.waitForTimeout(2000);
  
  // Debug: Check what's on the page after submission
  const alertCount = await page.locator('.alert').count();
  console.log('Alert elements found:', alertCount);
  console.log('Page content after submit:', (await page.textContent('body'))?.substring(0, 800));
  
  const alert = page.locator('.alert');
  await expect(alert).toBeVisible({ timeout: 10000 });

  // Values should persist
  await expect(page.locator('#join-form-name')).toHaveValue('Test User');
  await expect(page.locator('#join-form-email')).toHaveValue('invalid-email');
});

// Note: This success test assumes local DB is configured and reachable.
// It runs best in your Docker dev where clientregistration inserts succeed.

test('join success sets localStorage referral', async ({ page }) => {
  await page.goto('/join.php');
  // Wait longer for page to fully load and render form
  await page.waitForTimeout(3000);  
  // Debug: Check what's on the page if form not found
  const formExists = await page.locator('#join-form-name').count();
  if (formExists === 0) {
    console.log('Form not found. Page title:', await page.title());
    console.log('Page URL:', page.url());
    console.log('Body text (first 500 chars):', (await page.textContent('body'))?.substring(0, 500));
  }
    await expect(page.locator('#join-form-name')).toBeVisible({ timeout: 10000 });

  await page.fill('#join-form-name', 'Referral User');
  await page.fill('#join-form-email', 'referral@example.com');
  await page.fill('input[name="join-form-phone"]', '028-25578835');
  await page.fill('#join-form-message', 'Hi');

  // Avatar optional: leave blank
  await page.click('button[type="submit"]');

  // Wait for success alert
  const alert = page.locator('.alert');
  await expect(alert).toBeVisible();

  const ref = await page.evaluate(() => localStorage.getItem('ipnz_ref'));
  expect(ref).toBeTruthy();
  // v2.0: Alphanumeric referral codes (6 chars, e.g., GEWEEN, PU2VSQ)
  // Legacy format m\d+ still supported for backwards compat
  expect(ref).toMatch(/^([A-Z0-9]{6}|m\d+)$/);
});
