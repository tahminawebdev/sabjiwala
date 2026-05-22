import { test, expect } from '@playwright/test';

const PRODUCT_SLUG = 'nestle-cerelac-3-wheat-4-fruits-10-m';

test.describe('Shobjiwala production smoke', () => {
  test('GET /healthz returns 200 ok', async ({ request }) => {
    const res = await request.get('/healthz');
    expect(res.status()).toBe(200);
    expect((await res.text()).trim()).toBe('ok');
  });

  test('homepage loads with correct title', async ({ page }) => {
    const response = await page.goto('/');
    expect(response?.status()).toBe(200);
    await expect(page).toHaveTitle(/bengali grocery/i);
    await expect(page.locator('body')).toBeVisible();
  });

  test('homepage exposes the WooCommerce shop link', async ({ page }) => {
    await page.goto('/');
    await expect(page.locator('a[href$="/shop/"]').first()).toBeVisible();
  });

  test('shop page lists products', async ({ page }) => {
    const response = await page.goto('/shop/');
    expect(response?.status()).toBe(200);
    await expect(page.locator('a[href*="/product/"]').first()).toBeVisible();
  });

  test('single product page renders with add-to-cart', async ({ page }) => {
    const response = await page.goto(`/product/${PRODUCT_SLUG}/`);
    expect(response?.status()).toBe(200);
    await expect(page.locator('button.single_add_to_cart_button, .single_add_to_cart_button').first())
      .toBeVisible();
  });

  test('cart page loads (may be empty)', async ({ page }) => {
    const response = await page.goto('/cart/');
    expect(response?.status()).toBe(200);
  });

  test('my-account login form renders', async ({ page }) => {
    const response = await page.goto('/my-account/');
    expect(response?.status()).toBe(200);
    await expect(page.locator('input#username, input[name="username"]').first()).toBeVisible();
  });

  test('no PHP fatal error on homepage', async ({ page }) => {
    const response = await page.goto('/');
    const body = await response?.text() ?? '';
    expect(body).not.toMatch(/Fatal error|Parse error|critical error on this website/i);
  });

  test('site URLs were rewritten away from localhost:8080', async ({ request }) => {
    const res = await request.get('/');
    const body = await res.text();
    expect(body).not.toContain('localhost:8080');
  });

  test('xmlrpc.php is blocked at the LB', async ({ request }) => {
    const res = await request.post('/xmlrpc.php', { failOnStatusCode: false });
    expect(res.status()).toBe(403);
  });

  test('hidden files are 404 at the LB (security-in-depth)', async ({ request }) => {
    const res = await request.get('/.env', { failOnStatusCode: false });
    expect(res.status()).toBe(404);
  });
});
