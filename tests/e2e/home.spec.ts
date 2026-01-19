import { test, expect } from '@playwright/test';

// Verify homepage loads and X share intent builds with referral (if present)

test('homepage loads and X share builds intent', async ({ page }) => {
  // Stub window.open BEFORE navigation to capture calls
  await page.addInitScript(() => {
    (window as any).__openedUrl = null;
    const origOpen = window.open;
    window.open = function(url: string | URL, target?: string | undefined) {
      (window as any).__openedUrl = String(url);
      return null as any;
    };
  });

  await page.goto('/');
  await expect(page.locator('.navbar')).toBeVisible();

  await page.click('a#share-x');
  const opened = await page.evaluate('window.__openedUrl');
  expect(opened).toBeTruthy();
  expect(opened).toContain('intent/tweet');
  expect(opened).toMatch(/https:\/\/(?:ipnz|IPnz)\.live/i);
});
