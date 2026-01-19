import { test, expect } from '@playwright/test';

// Verify join form preserves data on validation error and sets referral on success
// v2.0: Tests updated for alphanumeric referral codes (6-char format, e.g., GEWEEN)

test('join form preserves inputs on invalid email', async ({ page }) => {
  await page.goto('/join.php');

  await page.fill('#join-form-name', 'Test User');
  await page.fill('#join-form-email', 'invalid-email');
  await page.fill('input[name="join-form-phone"]', '028-25578835');
  await page.fill('#join-form-message', 'Hello');

  await page.click('button[type="submit"]');

  // Expect error alert present
  const alert = page.locator('.alert');
  await expect(alert).toBeVisible();

  // Values should persist
  await expect(page.locator('#join-form-name')).toHaveValue('Test User');
  await expect(page.locator('#join-form-email')).toHaveValue('invalid-email');
});

// Note: This success test assumes local DB is configured and reachable.
// It runs best in your Docker dev where clientregistration inserts succeed.

test('join success sets localStorage referral', async ({ page }) => {
  await page.goto('/join.php');

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
